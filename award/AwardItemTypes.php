<?php

namespace go1\util\award;

class AwardItemTypes
{
    public const LO    = 'lo';
    public const LI    = 'li';
    public const AWARD = 'award';

    public static function all()
    {
        return [self::LO, self::LI, self::AWARD];
    }
}
