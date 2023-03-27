<?php

namespace go1\util\dimensions;

class DimensionType
{
    public const TOPIC                  = 1;
    public const INDUSTRY               = 2;
    public const REGION_RESTRICTION     = 3;
    public const LOCATION               = 4;
    public const BUSINESS_AREA          = 5;
    public const EXTERNAL_ACTIVITY_TYPE = 6;
    public const LEARNER_LEVEL          = 7;
    public const LOCALE                 = 8;
    public const REGION_RELEVANCE       = 9;
    public const ROLE_SKILL             = 10;
    public const PLAYBACK_TARGET        = 11;

    public static function all()
    {
        $rSelf = new \ReflectionClass(__CLASS__);
        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
