<?php

namespace go1\util\message_event\pipelines;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\message_event\MQMessageEvent;
use go1\util\portal\PortalHelper;
use Symfony\Component\HttpFoundation\Request;

class JWTEmbedded implements MessageEmbeddedPipeline
{
    private $req;
    private $instance;

    public function __construct(Request $req, string $instance = null)
    {
        $this->req = $req;
        $this->instance = $instance;
    }

    public function embed(MQMessageEvent $message): void
    {
        $payload = $message->jsonSerialize();

        $user = (new AccessChecker)->validUser($this->req, $this->instance);
        if ($user) {
            $payload['embedded']['jwt']['user'] = $user;
        }

        $message->setPayload($payload);
    }
}
