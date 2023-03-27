<?php

namespace go1\util\group;

class GroupItemStatus
{
    public const BLOCKED  = -3;
    public const REJECTED = -2;
    public const PENDING  = -1;
    public const ACTIVE   = 1;
    public const ALL      = [self::BLOCKED, self::REJECTED, self::PENDING, self::ACTIVE];

    public const PUBLISHED     = 1;
    public const UNPUBLISHED   = 0;
}
