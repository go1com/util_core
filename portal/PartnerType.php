<?php

namespace go1\util\portal;

use ReflectionClass;

class PartnerType
{
    public const COMPLISPACE = 'complispace';
    public const GO1 = 'go1';
    public const JOBREADY = 'jobready';
    public const JSE = 'jse';
    public const PARTNER_HUB = 'partnerhub';
    public const TOTARA = 'totara';
    public const XERO = 'xero';

    public static function all(): array
    {
        return array_values(
            (new ReflectionClass(__CLASS__))->getConstants()
        );
    }
}
