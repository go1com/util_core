<?php

namespace go1\util\message_event;

use Doctrine\DBAL\Connection;
use go1\util\message_event\pipelines\JWTEmbedded;
use go1\util\message_event\pipelines\PortalEmbedded;
use go1\util\user\UserHelper;
use Symfony\Component\HttpFoundation\Request;

class UserMQMessageEvent extends MQMessageEvent
{
    public function format(Connection $db, Request $req = null): void
    {
        $payload = (array) (new UserHelper())->format((object) $this->payload);
        $this->setPayload($payload);

        $this->pipelines = [
            new PortalEmbedded($db, $payload['instance'])
        ];
        $req && ($this->pipelines[] = new JWTEmbedded($req, $payload['instance']));

        $this->embed();
    }
}
