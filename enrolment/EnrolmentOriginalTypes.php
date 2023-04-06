<?php

namespace go1\util\enrolment;

class EnrolmentOriginalTypes
{
    public const SELF_DIRECTED = 'self-directed';
    public const ASSIGNED = 'assigned';

    public const I_SELF_DIRECTED = 1;
    public const I_ASSIGNED = 2;

    public static function toNumeric(string $type): int
    {
        switch ($type) {
            case self::SELF_DIRECTED:
                return self::I_SELF_DIRECTED;
            case self::ASSIGNED:
                return self::I_ASSIGNED;
            default:
                throw new InvalidArgumentException('Unknown original enrolment type: '.$type);
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::I_SELF_DIRECTED:
                return self::SELF_DIRECTED;
            case self::I_ASSIGNED:
                return self::ASSIGNED;
            default:
                throw new InvalidArgumentException('Unknown original enrolment type: '.$type);
        }
    }
}
