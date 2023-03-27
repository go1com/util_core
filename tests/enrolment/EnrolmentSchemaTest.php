<?php

namespace go1\util\tests\enrolment;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;
use go1\util\schema\EnrolmentSchema;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\tests\UtilCoreTestCase;

class EnrolmentSchemaTest extends UtilCoreTestCase
{
    use EnrolmentMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        DB::install($this->go1, [fn (Schema $schema) => EnrolmentSchema::installManualRecord($schema)]);
    }

    public function testUniqueOK()
    {
        $this->createEnrolment($this->go1, [
            'user_id'             => 111,
            'parent_lo_id'        => 222,
            'lo_id'               => 333,
            'parent_enrolment_id' => 444,
            'taken_instance_id'   => 555,
        ]);

        $this->createEnrolment($this->go1, [
            'user_id'             => 111,
            'parent_lo_id'        => 222,
            'lo_id'               => 333,
            'parent_enrolment_id' => 445, // should be OK
            'taken_instance_id'   => 555,
        ]);

        $this->assertTrue(true, 'No duplication error');
    }

    public function testUniqueError()
    {
        $this->expectException(Exception::class);
        $this->createEnrolment($this->go1, $sameRow = [
            'user_id'             => 111,
            'parent_lo_id'        => 222,
            'lo_id'               => 333,
            'parent_enrolment_id' => 444,
            'taken_instance_id'   => 555,
        ]);

        $this->createEnrolment($this->go1, $sameRow);
    }
}
