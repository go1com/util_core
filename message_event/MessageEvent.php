<?php

namespace go1\util\message_event;

use JsonSerializable;

interface MessageEvent extends JsonSerializable
{
    public function getRoutingKey(): string ;
    public function getContext(): array ;
}
