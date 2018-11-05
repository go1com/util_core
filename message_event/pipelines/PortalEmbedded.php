<?php

namespace go1\util\message_event\pipelines;

use Doctrine\DBAL\Connection;
use go1\util\message_event\MQMessageEvent;
use go1\util\portal\PortalHelper;

class PortalEmbedded implements MessageEmbeddedPipeline
{
    private $db;
    private $instance;

    public function __construct(Connection $db, $instance)
    {
        $this->db = $db;
        $this->instance = $instance;
    }

    public function embed(MQMessageEvent $message): void
    {
        $payload = $message->jsonSerialize();

        $portal = PortalHelper::load($this->db, $this->instance);
        if ($portal) {
            $payload['embedded']['portal'] = $portal;
        }

        $message->setPayload($payload);
    }
}
