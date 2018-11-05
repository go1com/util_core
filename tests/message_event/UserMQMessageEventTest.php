<?php

namespace go1\util\tests\message_event;

use go1\clients\MqClient;
use go1\util\message_event\UserMQMessageEvent;
use go1\util\queue\Queue;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\user\UserHelper;

class UserMQMessageEventTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use PortalMockTrait;

    private $mail       = 'abc@mail.com';
    private $profileId  = 123;
    private $instance   = 'qa.mygo1.com';

    public function testFormat()
    {
        $this->createPortal($this->db, ['title' => $this->instance]);
        $data = [
            'mail'       => $this->mail,
            'profile_id' => $this->profileId,
            'name'       => 'Bob Bay',
            'login'      => time(),
            'access'     => time(),
            'first_name' => 'Bob',
            'last_name'  => 'Bay',
            'status'     => 1,
        ];

        $userId = $this->createUser($this->db, $data + ['instance' => $this->instance]);
        $user = UserHelper::load($this->db, $userId);
        $context[MqClient::CONTEXT_PORTAL_NAME] = $this->instance;
        $context[MqClient::CONTEXT_ENTITY_TYPE] = 'user';
        $userMessage = new UserMQMessageEvent($user, Queue::USER_CREATE, $context);
        $userMessage->format($this->db);

        $payload = $userMessage->jsonSerialize();

        $this->assertEquals($userId, $payload->id);
        $embedded = $payload->embedded;
        $this->assertEquals($this->instance, $embedded['portal']->title);
    }
}
