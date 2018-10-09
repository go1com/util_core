<?php

namespace go1\util\tests\user\event_publishing;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use go1\util\user\event_publishing\AccountEventsEmbedder;
use go1\util\user\UserHelper;
use Symfony\Component\HttpFoundation\Request;

class AccountEventsEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;
    use EnrolmentMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $profileId = 999;
    protected $jwt;

    public function setUp()
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->db, ['instance' => $c['accounts_name'], 'profile_id' => $this->profileId]);
        $this->accountId = $this->createUser($this->db, ['instance' => 'qa.mygo1.com', 'profile_id' => $this->profileId]);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->db, $this->userId, 'qa.mygo1.com');
    }

    public function test()
    {
        $c = $this->getContainer();
        $embedder = new AccountEventsEmbedder($this->db, new $c['access_checker']);

        $account = UserHelper::load($this->db, $this->accountId);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embed($account, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->profileId, $embedded['jwt']['user']->profile_id);
    }
}
