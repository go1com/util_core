<?php

namespace go1\util\tests\error;

use go1\util\error\ApiErrorLogger;
use go1\util\tests\UtilCoreTestCase;
use Psr\Log\LoggerInterface;

class ApiErrorLoggerTest extends UtilCoreTestCase
{
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

        $class->logApiError(new \Exception(), 'Exception message');
    }
}
