<?php

namespace go1\util\message_event\pipelines;

use go1\util\message_event\MQMessageEvent;

interface MessageEmbeddedPipeline
{
    public function embed(MQMessageEvent $message): void;
}
