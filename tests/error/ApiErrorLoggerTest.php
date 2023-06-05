<?php

namespace go1\util\tests\error;

use go1\util\error\ApiErrorLogger;
use go1\util\tests\UtilCoreTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Prophecy\PhpUnit\ProphecyTrait;

class ApiErrorLoggerTest extends UtilCoreTestCase
{
    use ProphecyTrait;
    /**
     * Should log a message if the class contains logger interface
     */
    public function testDoNotSendLogWhenNotInitialized()
    {
        $loggerMock = $this->prophesize(LoggerInterface::class);
        $class = new class ($loggerMock->reveal()) {
            use ApiErrorLogger;

            public function __construct($logger)
            {
                $this->logger = $logger;
            }
        };

        $loggerMock->error('Exception message', Argument::any())->shouldBeCalled();
        $class->logApiError(new \Exception(), 'Exception message');
    }
}
