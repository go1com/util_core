<?php

namespace go1\util\award;

use go1\util\enrolment\EnrolmentStatuses;
use go1\util\plan\PlanStatuses;
use InvalidArgumentException;

class AwardEnrolmentStatuses
{
    public const IN_PROGRESS   = 1;
    public const COMPLETED     = 2;
    public const EXPIRED       = 3;
    public const NOT_STARTED   = 4;

    public const S_IN_PROGRESS     = 'in-progress';
    public const S_COMPLETED       = 'completed';
    public const S_EXPIRED         = 'expired';

    public static function all()
    {
        return [
            static::IN_PROGRESS,
            static::COMPLETED,
            static::EXPIRED,
            static::NOT_STARTED
        ];
    }

    public static function toString(int $status): string
    {
        switch ($status) {
            case self::IN_PROGRESS:
                return self::S_IN_PROGRESS;

            case self::COMPLETED:
                return self::S_COMPLETED;

            case self::EXPIRED:
                return self::S_EXPIRED;

            case self::NOT_STARTED:
                return EnrolmentStatuses::NOT_STARTED;

            default:
                throw new InvalidArgumentException('Unknown enrolment status: ' . $status);
        }
    }

    public static function toEsNumeric(int $status): int
    {
        switch ($status) {
            case self::IN_PROGRESS:
                return EnrolmentStatuses::I_IN_PROGRESS;

            case self::COMPLETED:
                return EnrolmentStatuses::I_COMPLETED;

            case self::EXPIRED:
                return EnrolmentStatuses::I_EXPIRED;

            case PlanStatuses::ASSIGNED:
                return PlanStatuses::ASSIGNED;

            case self::NOT_STARTED:
                return EnrolmentStatuses::I_NOT_STARTED;

            default:
                throw new InvalidArgumentException('Unknown enrolment status: ' . $status);
        }
    }
}
