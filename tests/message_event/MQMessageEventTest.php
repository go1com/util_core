<?php

namespace go1\util\tests\message_event;

use go1\util\message_event\MQMessageEvent;
use go1\util\tests\UtilCoreTestCase;

class MQMessageEventTest extends UtilCoreTestCase
{
    public function test()
    {
        $message = new MQMessageEvent(['key' => 1], 'routingKey', ['app' => 'unit_test']);

        $this->assertEquals('routingKey', $message->getRoutingKey());

        $context = $message->getContext();
        $this->assertEquals('unit_test', $context['app']);
        $this->assertArrayHasKey('timestamp', $context);

        $payload = $message->jsonSerialize();
        $this->assertEquals(1, $payload['key']);
    }

    public function testUpdateSuccess()
    {
        $payload = ['id' => 1, 'user' => 1];
        $payload['original'] = ['id' => 1, 'user' => 2];

        $message = new MQMessageEvent($payload, 'message.update');

        $payload = $message->jsonSerialize();
        $this->assertEquals(1, $payload['id']);

        $payload = (object) ['id' => 1, 'user' => 1];
        $payload->original = clone $payload;

        $message = new MQMessageEvent($payload, 'message.update');

        $payload = $message->jsonSerialize();
        $this->assertEquals(1, $payload->id);
    }
}
