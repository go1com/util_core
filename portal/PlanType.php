<?php

namespace go1\util\portal;

use InvalidArgumentException;

class PlanType
{
    public const PREMIUM          = 'premium';
    public const PLATFORM         = 'platform';
    public const INTERNAL         = 'internal';
    public const PLATFORM_PARTNER = 'platform_partner';

    public static function all()
    {
        return [self::PREMIUM, self::PLATFORM, self::INTERNAL, self::PLATFORM_PARTNER];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::PREMIUM:
                return 'Premium';

            case self::PLATFORM:
                return 'Platform';

            case self::INTERNAL:
                return 'Internal';

            case self::PLATFORM_PARTNER:
                return 'Platform Partner';

            default:
                throw new InvalidArgumentException('Unknown plan type: ' . $type);
        }
    }
}
