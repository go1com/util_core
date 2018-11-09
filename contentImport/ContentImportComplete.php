<?php
 namespace go1\util\ContentImport;

 use stdClass;

class ContentImportCompleteCreate
{
    const ROUTING_KEY = 'notify.content_import.complete';

    public static function publish(stdClass $body): stdClass
    {
      $contentJob = new stdClass();

      $contentJob->id = $body->id;
      $contentJob->type = $body->type;
      $contentJob->instanceId = $body->instanceId;
      $contentJob->configuration = $body->configuration;
      $contentJob->status = $body->status;
      $contentJob->priority = $body->priority;
      $contentJob->analytics = $body->analytics;
      $contentJob->reoccurringPeriod = $body->reoccurringPeriod;
      $contentJob->scheduleJobId = $body->scheduleJobId;
      $contentJob->createdDate = $body->createdDate;
      $contentJob->modifiedDate = $body->modifiedDate;
      $contentJob->message = $body->message;

      return $contentJob;
    }
}