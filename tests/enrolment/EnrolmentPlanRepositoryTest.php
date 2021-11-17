<?php

namespace go1\util\tests\enrolment;

use go1\util\enrolment\EnrolmentPlanRepository;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\plan\PlanTypes;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\tests\UtilCoreTestCase;

class EnrolmentPlanRepositoryTest extends UtilCoreTestCase
{
    use PlanMockTrait;
    use EnrolmentMockTrait;

    protected $planId;
    protected $enrolmentId;
    protected $rEnrolmentPlan;
    protected $entityType = 'lo';
    protected $entityId   = 111;
    protected $userId     = 222;
    protected $portalId   = 333;

    public function setUp(): void
    {
        parent::setUp();

        $this->rEnrolmentPlan = new EnrolmentPlanRepository($this->go1);

        $this->planId = $this->createPlan($this->go1, [
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'type'        => PlanTypes::ASSIGN,
        ]);

        $this->enrolmentId = $this->createEnrolment($this->go1, [
            'user_id'           => $this->userId,
            'taken_instance_id' => $this->portalId,
            'lo_id'             => $this->entityId,
            'status'            => EnrolmentStatuses::IN_PROGRESS,
        ]);
    }

    public function test()
    {
        $enrolmentPlanId = $this->rEnrolmentPlan->create($this->enrolmentId, $this->planId);
        $this->assertEquals(1, $enrolmentPlanId);
        $this->assertTrue($this->rEnrolmentPlan->has($this->enrolmentId, $this->planId));
    }
}
