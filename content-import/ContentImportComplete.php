<?php
 namespace go1\util\lo\event_publishing;
 use stdClass;
 class EventAttendanceCreate
{
    const ROUTING_KEY = 'notify.content_import.complete';

    public function publish(stdClass $body): stdClass
    {
      $contentJob = new stdClass();

      $contentJob = new ContentJob;
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