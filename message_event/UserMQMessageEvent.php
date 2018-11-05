<?php

namespace go1\util\message_event;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use Symfony\Component\HttpFoundation\Request;

class UserMQMessageEvent extends MQMessageEvent
{
    public function format(Connection $db = null, Request $req = null): void
    {
        $this->payload = (new UserHelper())->format($this->payload);

        $embedded = [];
        $portal = PortalHelper::load($db, $this->payload->instance);
        if ($portal) {
            $embedded['portal'] = $portal;
        }

        $user = $req ? (new AccessChecker)->validUser($req, $portal ? $portal->title : null) : null;
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        $this->payload->embedded = $embedded;
    }
}
