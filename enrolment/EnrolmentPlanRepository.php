<?php

namespace go1\util\enrolment;

use Doctrine\DBAL\Connection;

class EnrolmentPlanRepository
{
    private Connection $go1;

    public function __construct(Connection $go1)
    {
        $this->go1 = $go1;
    }

    public function create(int $enrolmentId, int $planId): int
    {
        $this->go1->insert('gc_enrolment_plans', [
            'enrolment_id' => $enrolmentId,
            'plan_id'      => $planId,
        ]);

        return $this->go1->lastInsertId('gc_enrolment_plans');
    }

    public function has(int $enrolmentId, int $planId): bool
    {
        $ok = $this->go1
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
}
