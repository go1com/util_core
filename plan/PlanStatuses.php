<?php

namespace go1\util\plan;

use ReflectionClass;

class PlanStatuses
{
    public const INTERESTING = -4; # Learner interest in the object, but no action provided yet.
    public const SCHEDULED   = -3; # Learner is scheduled in the object.
    public const ASSIGNED    = -2; # Learner self-assigned, or by someone.
    public const ENQUIRED    = -1; # Learner interesting in the object, enquired.
    public const PENDING     = 0; # The object is not yet available.
    public const LATE        = 4; # Learning was assigned & was not able to complete the plan ontime.
    public const EXPIRED     = 5; # The object is expired.

    public const S_INTERESTING = 'interesting';
    public const S_SCHEDULED   = 'scheduled';
    public const S_ASSIGNED    = 'assigned';
    public const S_ENQUIRED    = 'enquired';
    public const S_PENDING     = 'pending';
    public const S_LATE        = 'late';
    public const S_EXPIRED     = 'expired';

    public static function all(): array
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
