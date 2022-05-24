<?php

namespace go1\util\error;

use Psr\Log\LoggerInterface;

trait ApiErrorLogger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Log an error resulting from an internal API call.
     *
     * @param \Exception $exception
     * @param string     $message
     * @param array      $context
     */
    public function logApiError(\Exception $exception, string $message, array $context = [])
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error(
                $message,
                array_merge($context, ['exception' => $exception])
            );
        } else {
            trigger_error("No LoggerInterface set up in API class " . get_class($this), E_USER_WARNING);
        }
    }
}
