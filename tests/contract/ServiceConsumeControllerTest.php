<?php

namespace go1\util\tests\contract;

use go1\util\contract\ServiceConsumeController;
use go1\util\contract\ServiceConsumerInterface;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class ServiceConsumeControllerTest extends UtilCoreTestCase
{
    private $logs = [];

    public function testActorLogged()
    {
        $c = $this->getContainer();

        $c['consumers'] = [
            new class($this->logs) implements ServiceConsumerInterface {
                private array $logs;

                public function __construct(array &$logs)
                {
                    $this->logs = &$logs;
                }

                public function aware(): array
                {
                    return [
                        'foo' => 'QA',
                    ];
                }

                public function consume(string $routingKey, stdClass $body, stdClass $context = null)
                {
                    $this->logs[] = $context;
                }
            },
        ];

        /** @var ServiceConsumeController $controller */
        $controller = $c['service_consumer.controller'];

        $req = Request::create('/consume', 'POST', [
            'routingKey' => 'foo',
            'body'       => json_encode(['bar' => 'baz']),
        ]);

        $req->attributes->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $controller->post($req);
        $this->assertEquals(1, $this->logs[0]->activeUserId);
    }
}
