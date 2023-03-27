<?php

namespace go1\util\lo;

class SubscriptionAccessTypes
{
    public const NO_SUBSCRIPTION_NEEDED = 0;
    public const SUBSCRIPTION_NEEDED = 1;
    public const LICENSE_NEEDED = 2;
    public const LICENSED = 3;
    public const LICENSE_AVAILABLE = 4;

    public static function all()
    {
        return [
            self::NO_SUBSCRIPTION_NEEDED,
            self::SUBSCRIPTION_NEEDED,
            self::LICENSE_NEEDED,
            self::LICENSED,
            self::LICENSE_AVAILABLE
        ];
    }
}
