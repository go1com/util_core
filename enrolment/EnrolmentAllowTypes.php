<?php

namespace go1\util\enrolment;

use InvalidArgumentException;

class EnrolmentAllowTypes
{
    public const DEFAULT      = 'allow';
    public const ENROLL       = 'enroll'; // legacy data
    public const ENQUIRY      = 'enquiry';
    public const DISABLE      = 'disable';
    public const SUBSCRIPTION = 'subscription';

    // Numeric values for the types. Being used in ES.
    // allow > enquiry > subscription > disable
    public const I_DISABLE      = 0;
    public const I_ENQUIRY      = 10; // premium course also used the enquiry value
    public const I_SUBSCRIPTION = 5;
    public const I_DEFAULT      = 20;

    public static function toNumeric(string $type): int
    {
        switch ($type) {
            case self::DEFAULT:
            case self::ENROLL:
                return self::I_DEFAULT;

            case self::ENQUIRY:
                return self::I_ENQUIRY;

            case self::DISABLE:
                return self::I_DISABLE;

            case self::SUBSCRIPTION:
                return self::I_SUBSCRIPTION;

            default:
                throw new InvalidArgumentException('Unknown enrolment allow type: ' . $type);
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::I_DEFAULT:
                return self::DEFAULT;

            case self::I_ENQUIRY:
                return self::ENQUIRY;

            case self::I_DISABLE:
                return self::DISABLE;

            case self::I_SUBSCRIPTION:
                return self::SUBSCRIPTION;

            default:
                throw new InvalidArgumentException('Unknown enrolment allow type: ' . $type);
        }
    }
}
