<?php

namespace go1\util\message_event;

use go1\util\AccessChecker;
use go1\util\message_event\pipelines\MessageEmbeddedPipeline;
use go1\util\queue\Queue;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MQMessageEvent implements MessageEvent
{
    const CONTEXT_ACTOR_ID      = 'actor_id';
    /*** @deprecated */
    const CONTEXT_REQUEST_ID    = 'request_id';
    const CONTEXT_TIMESTAMP     = 'timestamp';

    protected $routingKey;
    protected $payload;
    protected $context = [];
    protected $pipelines = [];

    public function __construct($payload, string $routingKey, array $context = [])
    {
        $this->routingKey = $routingKey;
        $this->payload = is_scalar($payload) ? json_decode($payload) : (is_object($payload) ? (array) $payload : $payload);
        $this->context = $context;

        $this->processMessage();
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function setRoutingKey(string $routingKey): self
    {
        $this->routingKey = $routingKey;

        return $this;
    }

    public function setPayload($payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function jsonSerialize()
    {
        return $this->payload;
    }

    private function processMessage()
    {
        if ($this->routingKey == Queue::QUIZ_USER_ANSWER_UPDATE) {
            return null;
        }

        $explode = explode('.', $this->routingKey);
        $isLazy = isset($explode[0]) && ('do' == $explode[0]);

        if (strpos($this->routingKey, '.update') && !$isLazy) {
            if (substr($this->routingKey, 0, 5) === 'post_') {
                return null;
            }

            try {
                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->getValue($this->payload, '[id]');
                $accessor->getValue($this->payload, '[original]');
            } catch (Exception $e) {
                throw new Exception("Missing entity ID or original data.");
            }
        }

        if ($service = getenv('SERVICE_80_NAME')) {
            $this->context['app'] = $service;
        }

        if (!isset($this->context[self::CONTEXT_TIMESTAMP])) {
            $this->context[self::CONTEXT_TIMESTAMP] = time();
        }
    }

    public function addContextRequestId(Request $request)
    {
        if (!isset($this->context[self::CONTEXT_REQUEST_ID])) {
            if ($requestId = $request->headers->get('X-Request-Id')) {
                $this->context[self::CONTEXT_REQUEST_ID] = $requestId;
            }
        }
    }

    public function addContextActorId(Request $request)
    {
        if (!isset($this->context[self::CONTEXT_ACTOR_ID])) {
            $user = (new AccessChecker)->validUser($request);
            $user && $this->context[self::CONTEXT_ACTOR_ID] = $user->id;
        }
    }

    public function embed()
    {
        /** @var MessageEmbeddedPipeline $pipeline */
        foreach ($this->pipelines as $pipeline) {
            $pipeline->embed($this);
        }
    }
}
