<?php

namespace go1\util\award;

class AwardStatuses
{
    public const PENDING     = -2;
    public const ARCHIVED    = -1;
    public const UNPUBLISHED = 0;
    public const PUBLISHED   = 1;

    public static function all()
    {
        return [self::PENDING, self::ARCHIVED, self::UNPUBLISHED, self::PUBLISHED];
    }
}
