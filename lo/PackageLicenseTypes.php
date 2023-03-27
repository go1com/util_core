<?php

namespace go1\util\lo;

class PackageLicenseTypes
{
    public const PER_SEAT   = 1;
    public const PER_PORTAL = 2;

    public static function all()
    {
        return [self::PER_SEAT, self::PER_PORTAL];
    }
}
