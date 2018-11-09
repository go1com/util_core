<?php

namespace go1\util\tests\contentImport;

use go1\util\tests\UtilCoreTestCase;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\ContentImport\ContentImportCompleteCreate;

class ContentImportCompleteTest extends UtilCoreTestCase
{
    use PlanMockTrait;

    public function testContentImportCompleteMessage() {

      $messageBody = (object) [
          'id' => 1,
          'type' => 'CSV_Public',
          'instanceId'=> 1,
          'configuration' => '{ "data": "data" }',
          'status' => 'running',
          'priority' => 1,
          'analytics' => '{ "data": "data" }',
          'reoccurringPeriod' => 'weekly',
          'scheduleJobId' => 1,
          'createdDate' => date("Y-m-d H:i:s"),
          'modifiedDate' => date("Y-m-d H:i:s"),
          'message' => 'heh'
      ];

      $contentJob = ContentImportCompleteCreate::publish($messageBody);

        $this->assertEquals($contentJob->id, $messageBody->id);
        $this->assertEquals($contentJob->type, $messageBody->type);
        $this->assertEquals($contentJob->instanceId, $messageBody->instanceId);
        $this->assertEquals($contentJob->configuration, $messageBody->configuration);
        $this->assertEquals($contentJob->status, $messageBody->status);
        $this->assertEquals($contentJob->priority, $messageBody->priority);
        $this->assertEquals($contentJob->analytics, $messageBody->analytics);
        $this->assertEquals($contentJob->reoccurringPeriod, $messageBody->reoccurringPeriod);
        $this->assertEquals($contentJob->scheduleJobId, $messageBody->scheduleJobId);
        $this->assertEquals($contentJob->createdDate, $messageBody->createdDate);
        $this->assertEquals($contentJob->modifiedDate, $messageBody->modifiedDate);
    }
}