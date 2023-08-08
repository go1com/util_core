<?php

namespace go1\util\enrolment\event_publishing;

use Doctrine\DBAL\Connection;
use go1\core\util\client\federation_api\v1\PortalAccountMapper;
use go1\core\util\client\UserDomainHelper;
use go1\util\AccessChecker;
use go1\util\lo\LoHelper;
use go1\util\portal\PortalHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class EnrolmentEventsEmbedder
{
    private Connection $go1;
    private AccessChecker $access;
    private UserDomainHelper $userDomainHelper;

    public function __construct(Connection $go1, AccessChecker $access, UserDomainHelper $userDomainHelper)
    {
        $this->go1 = $go1;
        $this->access = $access;
        $this->userDomainHelper = $userDomainHelper;
    }

    public function embedded(stdClass $enrolment, Request $req = null): array
    {
        $embedded = [];

        $portal = PortalHelper::load($this->go1, $enrolment->taken_instance_id);
        if ($portal) {
            $embedded['portal'] = $portal;
            $loadUser = $this->userDomainHelper->loadUser($enrolment->user_id, $portal->title);
            $userAccount = $loadUser->account;
            if ($userAccount) {
                $embedded['account'] = PortalAccountMapper::toLegacyStandardFormat($loadUser, $userAccount, $portal);
            }
        }

        $lo = LoHelper::load($this->go1, $enrolment->lo_id);
        if ($lo) {
            $embedded['lo'] = $lo;
        }

        $user = $req ? $this->access->validUser($req, $portal ? $portal->title : null) : null;
        if ($user) {
            $embedded['jwt']['user'] = $user;
        }

        return $embedded;
    }
}
