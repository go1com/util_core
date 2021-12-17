<?php

namespace go1\util\tests\enrolment;

use go1\util\DateTime;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;

class EnrolmentMockTraitTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use EnrolmentMockTrait;
    use PlanMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $portalId;
    protected $portalPublicKey;
    protected $portalPrivateKey;
    protected $portalName = 'az.mygo1.com';
    protected $profileId  = 11;
    protected $userId, $jwt;
    protected $lpId, $courseId, $moduleId, $liVideoId, $liResourceId, $liInteractiveId, $electiveQuestionId, $electiveTextId, $electiveQuizId;

    public function setUp(): void
    {
        parent::setUp();

        // Create instance
        $this->portalId = $this->createPortal($this->go1, ['title' => $this->portalName]);
        $this->portalPublicKey = $this->createPortalPublicKey($this->go1, ['instance' => $this->portalName]);
        $this->portalPrivateKey = $this->createPortalPrivateKey($this->go1, ['instance' => $this->portalName]);
        $this->userId = $this->createUser($this->go1, ['instance' => $this->portalName]);
        $this->jwt = $this->getJwt();

        $data = json_encode(['elective_number' => 1]);
        $this->lpId = $this->createCourse($this->go1, ['type' => 'learning_pathway', 'instance_id' => $this->portalId]);
        $this->courseId = $this->createCourse($this->go1, ['type' => 'course', 'instance_id' => $this->portalId]);
        $this->moduleId = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId, 'data' => $data]);
        $this->liVideoId = $this->createCourse($this->go1, ['type' => 'video', 'instance_id' => $this->portalId]);
        $this->liResourceId = $this->createCourse($this->go1, ['type' => 'iframe', 'instance_id' => $this->portalId]);
        $this->liInteractiveId = $this->createCourse($this->go1, ['type' => 'interactive', 'instance_id' => $this->portalId]);
        $this->electiveQuestionId = $this->createCourse($this->go1, ['type' => 'question', 'instance_id' => $this->portalId]);
        $this->electiveTextId = $this->createCourse($this->go1, ['type' => 'text', 'instance_id' => $this->portalId]);
        $this->electiveQuizId = $this->createCourse($this->go1, ['type' => 'quiz', 'instance_id' => $this->portalId]);

        // Linking
        $this->link($this->go1, EdgeTypes::HAS_LP_ITEM, $this->lpId, $this->courseId, 0);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId, 0);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liVideoId, 0);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuestionId, 1);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveTextId, 2);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liResourceId, 3);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liInteractiveId, 4);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuizId, 5);
    }

    public function testCreate()
    {
        $enrolmentId = $this->createEnrolment($this->go1, [
            'user_id'           => $this->userId,
            'profile_id'        => $this->profileId,
            'taken_instance_id' => $this->portalId,
            'lo_id'             => $this->moduleId,
            'parent_lo_id'      => $this->courseId,
            'data'              => ['foo' => 'bar'],
        ]);

        $enrolment = EnrolmentHelper::loadSingle($this->go1, $enrolmentId);
        $this->assertEquals(EnrolmentStatuses::IN_PROGRESS, $enrolment->status);
        $this->assertEquals($this->profileId, $enrolment->profileId);
        $this->assertEquals($this->userId, $enrolment->userId);
        $this->assertEquals(0, $enrolment->instanceId);
        $this->assertEquals($this->portalId, $enrolment->takenPortalId);
        $this->assertEquals($this->moduleId, $enrolment->loId);
        $this->assertEquals($this->courseId, $enrolment->parentLoId);
        $this->assertEquals(0, $enrolment->parentEnrolmentId);
        $this->assertTrue(DateTime::atom('now') >= $enrolment->startDate);
        $this->assertNull($enrolment->endDate);
        $this->assertEquals(0, $enrolment->result);
        $this->assertEquals(0, $enrolment->pass);
        $this->assertTrue(time() >= $enrolment->timestamp);
        $this->assertTrue(time() >= $enrolment->changed);
        $this->assertEquals('bar', $enrolment->data->foo);
    }

    public function dataEnrolmentStatuses()
    {
        return [
            [EnrolmentStatuses::NOT_STARTED, null, null],
            [EnrolmentStatuses::IN_PROGRESS, DateTime::atom('now'), null],
        ];
    }

    /** @dataProvider dataEnrolmentStatuses */
    public function testCreateWithStatus($status, $expectedStartDate, $expectedEndDate)
    {
        $enrolmentId = $this->createEnrolment($this->go1, [
            'user_id'           => $this->userId,
            'profile_id'        => $this->profileId,
            'taken_instance_id' => $this->portalId,
            'lo_id'             => $this->moduleId,
            'parent_lo_id'      => $this->courseId,
            'status'            => $status,
            'data'              => ['foo' => 'bar'],
        ]);

        $enrolment = EnrolmentHelper::loadSingle($this->go1, $enrolmentId);
        $this->assertEquals($status, $enrolment->status);
        $this->assertTrue($expectedStartDate <= $enrolment->startDate,);
        $this->assertTrue($expectedEndDate <= $enrolment->endDate);
    }
}
