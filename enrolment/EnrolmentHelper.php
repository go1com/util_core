<?php

namespace go1\util\enrolment;

use DateTime as DefaultDateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use go1\core\util\client\federation_api\v1\schema\object\User;
use go1\core\util\client\federation_api\v1\UserMapper;
use go1\core\util\client\UserDomainHelper;
use go1\util\DateTime;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\model\Enrolment;
use go1\util\plan\PlanHelper;
use go1\util\plan\PlanTypes;
use LengthException;
use PDO;
use stdClass;

use function array_map;

/**
 * @TODO We're going to load & attach edges into enrolment.
 *
 *  - assessor
 *  - expiration
 *  - ...
 *
 * Format will like:
 *  $enrolment->edges[edge-type][] = edge
 */
class EnrolmentHelper
{
    public static function isEmbeddedPortalActive(stdClass $enrolment): bool
    {
        $portal = $enrolment->embedded->portal ?? null;

        return $portal ? $portal->status : true;
    }

    /**
     * @throws Exception
     */
    public static function enrolmentIdsByLoAndUser(Connection $db, int $loId, int $userId): array
    {
        return $db->fetchFirstColumn('SELECT id FROM gc_enrolment WHERE lo_id = ? AND user_id = ?', [$loId, $userId]);
    }

    public static function load(Connection $db, int $id, bool $loadEdges = false)
    {
        return ($enrolments = static::loadMultiple($db, [$id])) ? $enrolments[0] : false;
    }

    public static function loadMultiple(Connection $db, array $ids, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE id IN (?)', [$ids], [Connection::PARAM_INT_ARRAY])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByParentLo(Connection $db, int $parentLoId, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE parent_lo_id = ?', [$parentLoId])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByLo(Connection $db, int $loId, bool $loadEdges = false): array
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment WHERE lo_id = ?', [$loId])
            ->fetchAll(DB::OBJ);
    }

    public static function loadByLoAndUserId(Connection $db, int $loId, int $userId, int $parentLoId = null, $select = '*', $fetchMode = DB::OBJ): array
    {
        $q = $db
            ->createQueryBuilder()
            ->select($select)
            ->from('gc_enrolment')
            ->where('lo_id = :loId')->setParameter(':loId', $loId, DB::INTEGER)
            ->andWhere('user_id = :userId')->setParameter(':userId', $userId, DB::INTEGER);

        if ($parentLoId) {
            $q->andWhere('parent_lo_id = :parent_lo_id')->setParameter(':parent_lo_id', $parentLoId, DB::INTEGER);
        }

        return $q->execute()->fetchAll($fetchMode);
    }

    public static function loadRevision(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM gc_enrolment_revision WHERE enrolment_id = ?', [$id])
            ->fetch(DB::OBJ);
    }

    public static function becomeCompleted(stdClass $enrolment, stdClass $original, bool $passAware = true): bool
    {
        $status = $enrolment->status;
        $previousStatus = $original->status;

        if ($status != $previousStatus) {
            if (EnrolmentStatuses::COMPLETED === $status) {
                return $passAware ? (1 == $enrolment->pass) : true;
            }
        }

        return false;
    }

    public static function completed(stdClass $enrolment): bool
    {
        return ($enrolment->status == EnrolmentStatuses::COMPLETED) && ($enrolment->pass == 1);
    }

    # Check that all dependencies are completed.
    # Only return true if # of completion = # of dependencies
    public static function dependenciesCompleted(Connection $db, stdClass $enrolment, bool $passAware = true): bool
    {
        $moduleId = $enrolment->lo_id;
        $dependencyIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
        $dependencyIds = $db->executeQuery($dependencyIds, [EdgeTypes::HAS_MODULE_DEPENDENCY, $moduleId])->fetchAll(PDO::FETCH_COLUMN);
        if (!$dependencyIds) {
            return false; // If there's no dependencies -> input is wrong -> return false
        }

        if ($passAware) {
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id IN (?) AND status = ? AND pass = 1';
            $completion = $db->fetchColumn($completion, [$dependencyIds, EnrolmentStatuses::COMPLETED], 0, [DB::INTEGERS, DB::STRING]);
        } else {
            $completion = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id IN (?) AND status = ?';
            $completion = $db->fetchColumn($completion, [$dependencyIds], 0, [DB::INTEGERS]);
        }

        return $completion == count($dependencyIds);
    }

    public static function assessorIds(Connection $db, int $enrolmentId): array
    {
        return EdgeHelper::select('source_id')
            ->get($db, [], [$enrolmentId], [EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
    }

    public static function assessors(Connection $db, UserDomainHelper $userDomainHelper, int $enrolmentId): array
    {
        $assessorIds = self::assessorIds($db, $enrolmentId);
        $assessorIds = array_map('intval', $assessorIds);
        $assessors = !$assessorIds ? [] : array_map(
            function (User $user) {
                return UserMapper::toLegacyStandardFormat('', $user);
            },
            $userDomainHelper->loadMultipleUsers($assessorIds)
        );

        return $assessors;
    }

    /**
     * @deprecated
     * see EnrolmentHelper::parentEnrolment()
     */
    public static function findParentEnrolment(Connection $db, Enrolment $enrolment, $parentLoType = LoTypes::COURSE): ?Enrolment
    {
        $loadLo = function ($loId) use ($db) {
            return $db->executeQuery('SELECT id, type FROM gc_lo WHERE id = ?', [$loId])->fetch(DB::OBJ);
        };

        $parentQuery = function (stdClass $lo, Enrolment $enrolment) use ($db, $loadLo) {
            $parentLoId = $enrolment->parentLoId ?: false;
            if (empty($parentLoId)) {
                $query = $db->executeQuery('SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?', [EdgeTypes::LO_HAS_CHILDREN, $lo->id], [DB::INTEGERS, DB::INTEGER]);
                $parentLoId = $query->fetchColumn();
            }

            return [
                $parentLo = $parentLoId ? $loadLo($parentLoId) : false,
                $parentLo ? EnrolmentHelper::findEnrolment($db, $enrolment->takenPortalId, $enrolment->userId, $parentLo->id) : false,
            ];
        };
        $lo = $loadLo($enrolment->loId);
        [$parentLo, $parentEnrolment] = $parentQuery($lo, $enrolment);
        while ($parentLo && $parentEnrolment && ($parentLo->type != $parentLoType)) {
            [$parentLo, $parentEnrolment] = $parentQuery($parentLo, $parentEnrolment);
        }

        return $parentLo && ($parentLo->type == $parentLoType) ? $parentEnrolment : null;
    }

    public static function sequenceEnrolmentCompleted(Connection $db, int $loId, int $parentLoId, string $parentLoType = LoTypes::COURSE, int $userId = 0)
    {
        $edgeType = ($parentLoType == LoTypes::COURSE) ? EdgeTypes::LearningObjectTree['course'] : EdgeTypes::LearningObjectTree['module'];
        $requiredEdgeType = ($parentLoType == LoTypes::COURSE) ? EdgeTypes::HAS_MODULE : EdgeTypes::HAS_LI;

        // Fetching all LOs stay beyond current LO
        $loQuery = $db
            ->createQueryBuilder()
            ->select('required_ro.target_id')
            ->from('gc_ro', 'ro')
            ->join('ro', 'gc_ro', 'required_ro', 'ro.type = required_ro.type AND ro.source_id = required_ro.source_id')
            ->where('ro.type IN (:type)')->setParameter(':type', $edgeType, Connection::PARAM_INT_ARRAY)
            ->andwhere('required_ro.type = :requiredType')->setParameter(':requiredType', $requiredEdgeType)
            ->andWhere('ro.source_id = :source_id')->setParameter(':source_id', $parentLoId)
            ->andWhere('ro.target_id = :target_id')->setParameter(':target_id', $loId)
            ->andWhere('required_ro.weight < ro.weight');

        if (!$requiredLoIds = $loQuery->execute()->fetchAll(PDO::FETCH_COLUMN)) {
            return true;
        }

        // Fetching number of enrolled LO form above LoIds list
        $enrolmentQuery = $db
            ->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('gc_enrolment')
            ->where('lo_id IN (:lo_ids)')->setParameter(':lo_ids', $requiredLoIds, Connection::PARAM_INT_ARRAY)
            ->andWhere('user_id = :user_id')->setParameter(':user_id', $userId)
            ->andWhere('status = :status')->setParameter(':status', EnrolmentStatuses::COMPLETED);

        $completedRequiredLos = $enrolmentQuery->execute()->fetchColumn();

        return $completedRequiredLos >= count($requiredLoIds);
    }

    public static function childrenProgressCount(Connection $db, Enrolment $enrolment, $all = false, array $childTypes = [])
    {
        $childIds = LoHelper::childIds($db, $enrolment->loId, $all);
        $parentIds = array_merge($childIds, [$enrolment->loId]);
        if ($childIds && $childTypes) {
            $childIds = $db->executeQuery('SELECT id FROM gc_lo WHERE type IN (?) AND id IN (?)', [$childTypes, $childIds], [DB::STRINGS, DB::INTEGERS])->fetchAll(DB::COL);
        }
        $progress = ['total' => count($childIds)];
        if ($childIds) {
            $q = 'SELECT status, count(id) as totalEnrolment FROM gc_enrolment WHERE lo_id IN (?) AND user_id = ? AND parent_lo_id IN (?) GROUP BY status';
            $q = $db->executeQuery($q, [$childIds, $enrolment->userId, $parentIds], [DB::INTEGERS, DB::INTEGER, DB::INTEGERS]);
            while ($row = $q->fetch(DB::OBJ)) {
                $progress[$row->status] = $row->totalEnrolment;
            }
        }

        $numCompleted = $progress[EnrolmentStatuses::COMPLETED] ?? 0;
        $progress[EnrolmentStatuses::PERCENTAGE] = ($progress['total'] > 0) ? ($numCompleted / $progress['total']) : 0;
        $progress[EnrolmentStatuses::PERCENTAGE] = round($progress[EnrolmentStatuses::PERCENTAGE] * 100);

        return $progress;
    }

    /**
     * Returns the value of a single column of the first row of the result
     *
     * @param int    $portalId
     * @param int    $userId
     * @param int    $entityId
     * @param string $entityType
     * @return mixed|false False is returned if no rows are found.
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public static function loadUserPlanIdByEntity(Connection $go1, int $portalId, int $userId, int $entityId, string $entityType = 'lo')
    {
        return $go1
            ->createQueryBuilder()
            ->select('id')
            ->from('gc_plan')
            ->where('entity_type = :entityType')
            ->andWhere('entity_id = :entityId')
            ->andWhere('instance_id = :portalId')
            ->andWhere('user_id = :userId')
            ->setParameter(':entityType', $entityType, DB::STRING)
            ->setParameter(':entityId', $entityId, DB::INTEGER)
            ->setParameter(':portalId', $portalId, DB::INTEGER)
            ->setParameter(':userId', $userId, DB::INTEGER)
            ->execute()
            ->fetchOne();
    }

    public static function hasEnrolmentPlan(Connection $go1, int $enrolmentId, int $planId): bool
    {
        $ok = $go1
            ->createQueryBuilder()
            ->select('1')
            ->from('gc_enrolment_plans')
            ->where('enrolment_id = :enrolmentId')
            ->andWhere('plan_id = :planId')
            ->setParameter(':enrolmentId', $enrolmentId)
            ->setParameter(':planId', $planId)
            ->execute()
            ->fetchOne();

        return boolval($ok);
    }

    public static function createEnrolmentPlan(Connection $go1, int $enrolmentId, int $planId)
    {
        $go1->insert('gc_enrolment_plans', [
            'enrolment_id' => $enrolmentId,
            'plan_id'      => $planId,
        ]);
    }

    public static function countUserEnrolment(Connection $db, int $userId, int $takenInstanceId = null): int
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('count(*)')
            ->from('gc_enrolment')
            ->where('user_id = :user_id')
            ->setParameter('user_id', $userId);

        if ($takenInstanceId) {
            $q
                ->andWhere('taken_instance_id = :taken_instance_id')
                ->setParameter('taken_instance_id', $takenInstanceId);
        }
        return $q->execute()->fetchColumn();
    }

    public static function dueDate(Connection $db, int $enrolmentId): ?DefaultDateTime
    {
        $edges = $db
            ->executeQuery('SELECT * FROM gc_enrolment_plans WHERE enrolment_id = ?', [$enrolmentId]);

        $dueDate = null;
        while ($edge = $edges->fetch(PDO::FETCH_OBJ)) {
            if ($plan = PlanHelper::load($db, $edge->plan_id)) {
                if ($plan->due_date && (PlanTypes::ASSIGN == $plan->type)) {
                    return DateTime::create($plan->due_date);
                }

                if ($plan->due_date) {
                    $dueDate = DateTime::create($plan->due_date);
                }
            }
        }

        return $dueDate;
    }

    /**
     * If there is a completion rule and the due date is not set elsewhere by admin, users have that due date attached.
     * If there is a completion rule but the admin sets a due date via a group, it should be overwritten for users in that group.
     * On top of the above, if a user is separately assigned by admin, that will overwrite the group due date / completion rule date).
     *
     * @param \Doctrine\DBAL\Connection $db
     * @param int                       $enrolmentId
     * @return array
     */
    public static function getDueDateAndPlanType(Connection $db, int $enrolmentId): array
    {
        $edges = $db
            ->executeQuery('SELECT * FROM gc_enrolment_plans WHERE enrolment_id = ?', [$enrolmentId]);
        $dueDate = null;
        $planType = null;
        while ($edge = $edges->fetch(PDO::FETCH_OBJ)) {
            $plan = PlanHelper::load($db, $edge->plan_id);
            if ($plan && $plan->due_date && (PlanTypes::ASSIGN == $plan->type)) {
                $dueDate = DateTime::create($plan->due_date);
                $planType = $plan->type;
                break;
            }

            if ($plan && $plan->due_date) {
                $dueDate = DateTime::create($plan->due_date);
                $planType = $plan->type;
            }
        }

        return [$dueDate, $planType];
    }

    public static function findEnrolment(Connection $db, int $portalId, int $userId, int $loId, int $parentEnrolmentId = null): ?Enrolment
    {
        $q = $db
            ->createQueryBuilder()
            ->select('*')
            ->from('gc_enrolment')
            ->where('lo_id = :loId')->setParameter(':loId', $loId)
            ->andWhere('user_id = :userId')->setParameter(':userId', $userId)
            ->andWhere('taken_instance_id = :takenInstanceId')->setParameter(':takenInstanceId', $portalId);

        if (!is_null($parentEnrolmentId)) {
            $q
                ->andWhere('parent_enrolment_id = :parentEnrolmentId')
                ->setParameter(':parentEnrolmentId', $parentEnrolmentId);
        }
        $row = $q->execute()->fetch(DB::OBJ);

        return $row ? Enrolment::create($row) : null;
    }

    public static function childIds(Connection $db, int $enrolmentId): array
    {
        return $db
            ->createQueryBuilder()
            ->select('id')
            ->from('gc_enrolment')
            ->where('parent_enrolment_id = :parentEnrolmentId')
            ->setParameter(':parentEnrolmentId', $enrolmentId)
            ->execute()
            ->fetchAll(DB::COL);
    }

    public static function loadSingle(Connection $db, int $enrolmentId): ?Enrolment
    {
        $row = 'SELECT * FROM gc_enrolment WHERE id = ?';
        $row = $db->executeQuery($row, [$enrolmentId], [DB::INTEGER])->fetch(DB::OBJ);

        return $row ? Enrolment::create($row) : null;
    }

    public static function parentEnrolment(Connection $db, Enrolment $enrolment, $parentLoType = LoTypes::COURSE): ?Enrolment
    {
        if ($db->fetchColumn('SELECT 1 FROM gc_lo WHERE type = ? AND id = ?', [$parentLoType, $enrolment->loId])) {
            return $enrolment;
        }
        $parentEnrolment = $enrolment->parentEnrolmentId ? EnrolmentHelper::loadSingle($db, $enrolment->parentEnrolmentId) : null;

        return $parentEnrolment ? static::parentEnrolment($db, $parentEnrolment, $parentLoType) : null;
    }
}
