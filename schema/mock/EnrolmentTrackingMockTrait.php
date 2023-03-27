<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait EnrolmentTrackingMockTrait
{
    public function createEnrolmentTracking(Connection $db, array $options = []): void
    {
        $db->insert('enrolment_tracking', [
            'enrolment_id' => $options['enrolment_id'],
            'action_origin' => $options['action_origin'],
            'actor_id' => $options['actor_id'],
            'channel' => $options['channel']
        ]);
    }
}
