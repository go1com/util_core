<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\UtilCoreServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Log\LoggerInterface;

class UtilCoreTestCase extends TestCase
{
    use InstallTrait;
    use UserMockTrait;
    use QueueMockTrait;

    /** @var  Connection */
    protected $db;
    protected $log;

    /** @var MqClient */
    protected $queue;
    protected $queueMessages = [];

    public function setUp()
    {
        $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->db, false);

        DB::install($this->db, [
            function (Schema $schema) {
                $this->setupDatabaseSchema($schema);
            },
        ]);

        $this->queue = $this
            ->getMockBuilder(MqClient::class)
            ->setMethods(['publish', 'queue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->queue
            ->expects($this->any())
            ->method('publish')
            ->willReturnCallback(
                $callback = function ($payload, $subject, $context = null) {
                    if ($context && is_array($payload)) {
                        $payload['_context'] = $context;
                    }

                    $this->queueMessages[$subject][] = $payload;
                }
            );

        $this
            ->queue
            ->expects($this->any())
            ->method('queue')
            ->willReturnCallback($callback);
    }

    protected function setupDatabaseSchema(Schema $schema)
    {
        # Extra database setup, test cases can safely override this.
    }

    protected function getContainer(bool $rebuild = false): Container
    {
        static $container;

        if (null === $container || $rebuild) {
            $container = new Container(['accounts_name' => 'accounts.test']);
            $container->register(new UtilCoreServiceProvider, [
                'logger' => function () {
                    $logger = $this
                        ->getMockBuilder(LoggerInterface::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['error'])
                        ->getMockForAbstractClass();

                    $logger
                        ->expects($this->any())
                        ->method('error')
                        ->willReturnCallback(function ($message) {
                            $this->log['error'][] = $message;
                        });

                    return $logger;
                },
            ]);

            $this->setupContainer($container);
        }

        return $container;
    }

    public function setupContainer(Container &$c)
    {
        # Extra container setup, test cases can safely override this.
    }
}
