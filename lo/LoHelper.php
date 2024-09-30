<?php

namespace go1\util\lo;

use Doctrine\DBAL\Connection;
use go1\core\util\client\federation_api\v1\schema\object\User;
use go1\core\util\client\federation_api\v1\UserMapper;
use go1\core\util\client\UserDomainHelper;
use go1\util\Currency;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentHelper;
use HTMLPurifier_Config;
use PDO;
use stdClass;

use function array_map;

class LoHelper
{
    # configuration key for LO, which put under gc_lo.data
    # ---------------------
    public const DISCUSSION_ALLOW = 'allow_discussion';
    public const ENROLMENT_ALLOW  = 'allow_enrolment';
    /** @deprecated */
    public const ENROLMENT_ALLOW_DEFAULT   = 'allow';
    public const ASSIGNMENT_ALLOW_RESUBMIT = 'allow_resubmit';
    /** @deprecated */
    public const ENROLMENT_ALLOW_DISABLE = 'disable';
    /** @deprecated */
    public const ENROLMENT_ALLOW_ENQUIRY    = 'enquiry';
    public const ENROLMENT_RE_ENROL         = 're_enrol';
    public const ENROLMENT_RE_ENROL_DEFAULT = true;
    public const MANUAL_PAYMENT             = 'manual_payment';
    public const MANUAL_PAYMENT_RECIPIENT   = 'manual_payment_recipient';
    public const SEQUENCE_ENROL             = 'requiredSequence';
    public const SUGGESTED_COMPLETION_TIME  = 'suggested_completion_time';
    public const SUGGESTED_COMPLETION_UNIT  = 'suggested_completion_unit';
    public const PASS_RATE                  = 'pass_rate';
    public const SINGLE_LI                  = 'single_li';
    public const ALLOW_REUSE_ENROLMENT      = 'allow_reuse_enrolment'; // Use existing enrollments for reused content

    // GO1P-5665: Expiration for award.
    public const AWARD      = 'award';
    public const AWARD_TYPE = [
        'quantity'   => ['type' => 'bool', 'default' => false],
        'expiration' => ['type' => 'string', 'default' => '+ 1 year'],
    ];

    # I was able to import stdClass before, now sometime, I can't!
    public static function isEmbeddedPortalActive(\stdClass $lo): bool
    {
        $portal = $lo->embedded->portal ?? null;

        return $portal ? $portal->status : true;
    }

    public static function loadOrGetFromEmbeddedData(Connection $go1, stdClass $payload, string $loIdProperty = 'lo_id')
    {
        return $payload->embedded->lo ?? self::load($go1, $payload->{$loIdProperty});
    }

    
    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function load(Connection $go1, int $id, int $portalId = null, bool $expensiveTree = false, bool $attachAttributes = false)
    {
        return ($learningObjects = static::loadMultiple($go1, [$id], $portalId, $expensiveTree, $attachAttributes)) ? $learningObjects[0] : false;
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function loadMultiple(Connection $db, array $ids, int $portalId = null, bool $expensiveTree = false, bool $attachAttributes = false): array
    {
        $ids = array_map('intval', $ids);
        $learningObjects = !$ids ? [] : $db
            ->executeQuery(
                'SELECT lo.*, pricing.price, pricing.currency, pricing.tax, pricing.tax_included, pricing.recurring'
                . ' FROM gc_lo lo'
                . ' LEFT JOIN gc_lo_pricing pricing ON lo.id = pricing.id'
                . ' WHERE lo.id IN (?)',
                [$ids],
                [DB::INTEGERS]
            )
            ->fetchAll(DB::OBJ);

        $loIds = [];
        foreach ($learningObjects as &$lo) {
            if (!$lo->data = json_decode($lo->data)) {
                unset($lo->data);
            }

            $lo->pricing = (object) [
                'price'        => $lo->price ? (float) $lo->price : 0.00,
                'currency'     => $lo->currency ?: Currency::DEFAULT,
                'tax'          => $lo->tax ? (float) $lo->tax : 0.00,
                'tax_included' => $lo->tax_included ? true : false,
                'recurring'    => $lo->recurring ? json_decode($lo->recurring) : null,
            ];
            unset($lo->price, $lo->currency, $lo->tax, $lo->tax_included);

            unset($lo->hashed_source_id); // this is a MD5 hashed value of data.source_id & internal to the LO service only.

            $lo->event = new stdClass();
            $loIds[] = $lo->id;
        }

        if ($loIds) {
            if ($portalId) {
                # Load custom tags.
                $q = 'SELECT lo_id, tag FROM gc_lo_tag WHERE status = 1 AND instance_id = ? AND lo_id IN (?)';
                $q = $db->executeQuery($q, [$portalId, $loIds], [DB::INTEGER, DB::INTEGERS]);
                while ($row = $q->fetch(DB::OBJ)) {
                    foreach ($learningObjects as &$lo) {
                        if ($lo->id == $row->lo_id) {
                            $lo->custom_tags[] = $row->tag;
                        }
                    }
                }
            }

            if ($expensiveTree) {
                $load = function (array &$nodes, array &$nodeIds, array $edgeTypes) use (&$db, $portalId) {
                    $itemIds = [];
                    $q = 'SELECT source_id, target_id FROM gc_ro WHERE source_id IN (?) AND type IN (?) ORDER BY weight';
                    $q = $db->executeQuery($q, [$nodeIds, $edgeTypes], [DB::INTEGERS, DB::INTEGERS]);

                    while ($edge = $q->fetch(DB::OBJ)) {
                        foreach ($nodes as &$node) {
                            if ($node->id == $edge->source_id) {
                                $itemIds[] = (int) $edge->target_id;
                                $node->items[] = (object) ['id' => (int) $edge->target_id];
                            }
                        }
                    }

                    if ($itemIds && $items = self::loadMultiple($db, $itemIds, $portalId, true)) {
                        foreach ($items as &$item) {
                            foreach ($nodes as &$node) {
                                if (!empty($node->items)) {
                                    foreach ($node->items as &$_) {
                                        if ($_->id == $item->id) {
                                            $_ = $item;
                                        }
                                    }
                                }
                            }
                        }
                    }
                };

                $courses = $courseIds = $modules = $moduleIds = [];

                foreach ($learningObjects as &$lo) {
                    if (LoTypes::COURSE == $lo->type) {
                        $courses[] = &$lo;
                        $courseIds[] = (int) $lo->id;
                    }

                    if (LoTypes::MODULE == $lo->type) {
                        $modules[] = &$lo;
                        $moduleIds[] = (int) $lo->id;
                    }
                }

                $courseIds && $load($courses, $courseIds, [EdgeTypes::HAS_ELECTIVE_LO, EdgeTypes::HAS_MODULE, EdgeTypes::HAS_EVENT_EDGE, EdgeTypes::HAS_ELECTIVE_LI, EdgeTypes::HAS_LI]);
                $moduleIds && $load($modules, $moduleIds, [EdgeTypes::HAS_ELECTIVE_LI, EdgeTypes::HAS_LI]);
            }
        }

        if ($attachAttributes) {
            $attributes = self::getAttributes($db, $loIds);
            foreach ($learningObjects as &$lo) {
                $lo->attributes = (object) ($attributes[$lo->id] ?? []);
            }
        }

        return $learningObjects;
    }

    private static function getAttributes(Connection $db, array $ids)
    {
        $arr = [];
        try {
            $qb = $db
                ->createQueryBuilder()
                ->select('DISTINCT gc_lo_attributes.lo_id, gc_lo_attributes.key', 'gc_lo_attributes.value', 'lookup.attribute_type', 'lookup.is_array', 'lo.type', 'lookup.dimension_id')
                ->from('gc_lo_attributes')
                ->join('gc_lo_attributes', 'gc_lo', 'lo', 'gc_lo_attributes.lo_id = lo.id')
                ->leftJoin('gc_lo_attributes', 'gc_lo_attributes_lookup', 'lookup', 'gc_lo_attributes.key = lookup.key')
                ->andWhere('lo_id in (:lo_id)')
                ->setParameter(':lo_id', $ids, DB::INTEGERS);

            $attributes = $qb
                ->execute()
                ->fetchAll(DB::OBJ);

            foreach ($attributes as $attribute) {
                if (in_array($attribute->key, LoAttributes::all())) {
                    $_ = LoAttributes::machineName($attribute->key);
                    $atts = new StdClass();
                    $atts->isArray = $attribute->is_array;
                    $atts->dimensionId = $attribute->dimension_id;
                    $atts->loId = $attribute->lo_id;
                    $atts->attributeType = $attribute->attribute_type;
                    if ($atts->isArray) {
                        $arr[$attribute->lo_id][$_][] = self::formatAttributeValue($attribute->value, $atts);
                    } else {
                        $arr[$attribute->lo_id][$_] = self::formatAttributeValue($attribute->value, $atts);
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing, this is here in case it is used before the required tables are added
        }

        return $arr;
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function loadMultipleFieldsOnly(Connection $db, array $ids, array $fields = null): array
    {
        $ids = array_map('intval', $ids);
        $fieldsStr = $fields ? implode(",", $fields) : '*';

        $learningObjects = !$ids ? [] : $db
            ->executeQuery(
                "SELECT $fieldsStr FROM gc_lo WHERE id IN (?)",
                [$ids],
                [DB::INTEGERS]
            )
            ->fetchAll(DB::OBJ);

        return $learningObjects;
    }

    public static function findIds(array &$items, array &$ids = [])
    {
        foreach ($items as &$item) {
            $ids[] = $item['id'];

            if (!empty($item['items'])) {
                static::findIds($item['items'], $ids);
            }
        }
    }

    /**
     * Filter learning object description by below elements
     * Iframe: allow YouTube and Vimeo
     */
    public static function descriptionPurifierConfig()
    {
        $cnf = HTMLPurifier_Config::createDefault();
        $cnf->set('Cache.DefinitionImpl', null);
        $cnf->set('HTML.AllowedElements', [
            'b', 'code', 'del', 'dd', 'dl', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'sup', 'sub', 'div', 'p', 'blockquote', 'strong', 'i', 'kbd', 's',
            'strike', 'hr', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot', 'em', 'pre', 'br',
            'table', 'a', 'iframe', 'img', 'ul', 'li', 'ol', 'caption', 'span', 'u',
        ]);
        $cnf->set('HTML.AllowedAttributes', [
            'a.href', 'a.rel', 'a.target',
            'img.src', 'img.width', 'img.height', 'img.style',
            'table.width', 'table.cellspacing', 'table.cellpadding', 'table.height', 'table.align', 'table.summary', 'table.style',
            '*.class', '*.alt', '*.title', '*.border',
            'div.data-oembed-url', 'div.style', 'span.style',
            'iframe.src', 'iframe.allowfullscreen', 'iframe.width', 'iframe.height',
            'iframe.frameborder', 'iframe.mozallowfullscreen', 'iframe.webkitallowfullscreen',
        ]);
        $cnf->set('HTML.SafeIframe', true);
        $cnf->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|fast\.wistia\.net\/embed/|video\.blueoceanacademy\.cn)%');
        $cnf->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);

        $def = $cnf->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'mozallowfullscreen', 'Bool');
        $def->addAttribute('iframe', 'webkitallowfullscreen', 'Bool');
        $def->addAttribute('div', 'data-oembed-url', 'CDATA');
        $def->addAttribute('table', 'height', 'Number');

        return $cnf;
    }

    public static function getTitlePurifyConfig()
    {
        $cnf = HTMLPurifier_Config::createDefault();
        $cnf->set('Cache.DefinitionImpl', null);
        $cnf->set('HTML.Allowed', '');
        $cnf->set('Core.HiddenElements', []);

        return $cnf;
    }

    public static function sanitizeTitle(string $title = "")
    {
        // HTML Decode Characters, replace br and new lines with spaces
        $title = preg_replace("/(<br\W*?\/?>)|(\s+)/im", " ", html_entity_decode($title, ENT_QUOTES));

        // Strip tags and trim
        return trim(strip_tags($title));
    }

    public static function assessorIds(Connection $db, int $loId): array
    {
        return EdgeHelper::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::COURSE_ASSESSOR], PDO::FETCH_COLUMN);
    }

    public static function enrolmentAssessorIds(Connection $db, int $loId, int $learnerUserId): array
    {
        if ($enrolmentIds = EnrolmentHelper::enrolmentIdsByLoAndUser($db, $loId, $learnerUserId)) {
            return EdgeHelper::select('source_id')
                ->get($db, [], $enrolmentIds, [EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
        }

        return [];
    }

    public static function activeMembershipIds(Connection $social, int $loId): array
    {
        $groupIds = 'SELECT group_id FROM social_group_item WHERE entity_type = ? AND entity_id = ?';
        $groupIds = $social->executeQuery($groupIds, ['lo', $loId])->fetchAll(PDO::FETCH_COLUMN);

        return !$groupIds ? [] : $social
            ->executeQuery(
                'SELECT DISTINCT entity_id FROM social_group_item WHERE entity_type = ? AND group_id IN (?)',
                ['portal', $groupIds],
                [DB::STRING, DB::INTEGERS]
            )
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function parentIds(Connection $db, int $loId, $allParent = true): array
    {
        $q = 'SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $allParent && $ids = array_merge($ids, static::parentIds($db, $id));
            $ids[] = (int) $id;
        }

        return array_unique($ids);
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function parentsAuthorIds(Connection $db, int $loId, array $parentLoIds = null): array
    {
        $authorIds = [];
        if (!isset($parentLoIds)) {
            $parentLoIds = static::parentIds($db, $loId);
        }
        $parentLoIds[] = $loId;

        foreach ($parentLoIds as $parentLoId) {
            $authorIds = array_merge($authorIds, self::authorIds($db, $parentLoId));
        }

        $authorIds = array_values(array_unique($authorIds));

        return array_map('intval', $authorIds);
    }

    public static function parentsAssessorIds(Connection $db, int $loId, array $parentLoIds = null, int $learnerUserId = null): array
    {
        $assessorIds = [];
        if (!isset($parentLoIds)) {
            $parentLoIds = static::parentIds($db, $loId);
        }
        $parentLoIds[] = $loId;

        foreach ($parentLoIds as $parentLoId) {
            $assessorIds = array_merge($assessorIds, self::assessorIds($db, $parentLoId));

            if ($learnerUserId) {
                $assessorIds = array_merge($assessorIds, self::enrolmentAssessorIds($db, $parentLoId, $learnerUserId));
            }
        }

        $assessorIds = array_values(array_unique($assessorIds));

        return array_map('intval', $assessorIds);
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function childIds(Connection $db, int $loId, $all = false): array
    {
        $q = 'SELECT target_id FROM gc_ro WHERE type IN (?) AND source_id = ?';
        $q = $db->executeQuery($q, [EdgeTypes::LO_HAS_CHILDREN, $loId], [DB::INTEGERS, DB::INTEGER]);

        $ids = [];
        while ($id = $q->fetchColumn()) {
            $all && $ids = array_merge($ids, static::childIds($db, $id));
            $ids[] = (int) $id;
        }

        return $ids;
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function moduleIdToCourseId(Connection $db, int $moduleId): int
    {
        $_ = 'SELECT source_id FROM gc_ro WHERE (type = ? OR type = ?) AND target_id = ?';
        $_ = $db->fetchColumn($_, [EdgeTypes::HAS_MODULE, EdgeTypes::HAS_ELECTIVE_LO, $moduleId]);

        return (int) $_;
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function moduleIds(Connection $db, int $loId): array
    {
        return EdgeHelper::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::HAS_MODULE, EdgeTypes::HAS_ELECTIVE_LO], PDO::FETCH_COLUMN);
    }

    public static function countEnrolment(Connection $db, int $loId)
    {
        $sql = 'SELECT COUNT(*) FROM gc_enrolment WHERE lo_id = ?';

        return $db->fetchColumn($sql, [$loId]);
    }

    public static function getCustomisation(Connection $db, int $loId, int $portalId): array
    {
        $edges = EdgeHelper::edges($db, [$loId], [$portalId], [EdgeTypes::HAS_LO_CUSTOMISATION]);
        if ($edge = reset($edges)) {
            $data = $edge->data ?? [];

            return is_scalar($data) ? json_decode($data, true) : [];
        }

        return [];
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function isSingleLi(stdClass $lo): bool
    {
        $isSingleLi = $lo->single_li ?? $lo->data->{self::SINGLE_LI} ?? false;

        return in_array($lo->type, LiTypes::all())
            ? boolval($isSingleLi)
            : false;
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function authorIds(Connection $db, int $loId): array
    {
        return EdgeHelper::select('target_id')
            ->get($db, [$loId], [], [EdgeTypes::HAS_AUTHOR_EDGE], PDO::FETCH_COLUMN);
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function authors(Connection $db, UserDomainHelper $userDomainHelper, int $loId): array
    {
        $authorIds = self::authorIds($db, $loId);
        $authorIds = array_map('intval', $authorIds);
        $authors = !$authorIds ? [] : array_map(
            function (User $user) {
                return UserMapper::toLegacyStandardFormat('', $user);
            },
            $userDomainHelper->loadMultipleUsers($authorIds)
        );

        return $authors;
    }

    public static function getSuggestedCompletion(Connection $db, int $loId, int $parentId = 0): array
    {
        if ($lo = LoHelper::load($db, $loId)) {
            $types = [EdgeTypes::HAS_SUGGESTED_COMPLETION];
            $targetId = $parentId ? EdgeHelper::hasLink($db, EdgeTypes::HAS_LI, $parentId, $lo->id) : 0;
            $edges = $targetId ? EdgeHelper::edges($db, [$lo->id], [$targetId], $types) : EdgeHelper::edgesFromSource($db, $lo->id, $types);
            if ($edge = reset($edges)) {
                $data = (is_scalar($edge->data)) ? json_decode($edge->data, true) : [];

                return [
                    $data['type'],
                    $data['value'],
                ];
            }
        }

        return [];
    }

    /**
     * @deprecated Visit https://go1web.atlassian.net/wiki/spaces/PE/pages/3484975224/Migrating+from+PHP+Go1+Utils#Migrating-util_core%5CLoHelper-functions for migration steps to the 1Content system
     */
    public static function countChild(Connection $db, int $id): int
    {
        if (!$childrenId = LoHelper::childIds($db, $id, true)) {
            return 0;
        }

        $result = 0;
        $sql = 'SELECT type, COUNT(*) as count FROM gc_lo WHERE type IN (?) AND id IN (?) GROUP BY type';
        $rows = $db->executeQuery($sql, [LiTypes::all(), $childrenId], [DB::STRINGS, DB::INTEGERS])->fetchAll();

        foreach ($rows as $row) {
            if ($row['type'] == LiTypes::EVENT && $row['count'] > 0) {
                $result++;
            } else {
                $result += $row['count'];
            }
        }

        return $result;
    }

    public static function allowReuseEnrolment(stdClass $lo): bool
    {
        return boolval($lo->data->{self::ALLOW_REUSE_ENROLMENT} ?? false);
    }

    public static function formatAttributeValue($value, $lookup)
    {
        if (empty($lookup)) {
            return $value;
        }

        if ($lookup->isArray) {
            $tempValue = json_decode($value);
            if (!is_null($tempValue) && !is_numeric($tempValue)) {
                $value = $tempValue;
            } else {
                if ($lookup->attributeType == LoAttributeTypes::TEXT) {
                    $value = "$value";
                }
            }
        }

        return $value;
    }
}
