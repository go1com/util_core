<?php

namespace go1\util\lo;

use ReflectionClass;

class LoAttributeTypes
{
    public const BOOLEAN   = "BOOLEAN";
    public const INTEGER   = "INTEGER";
    public const TEXT      = "TEXT";
    public const DIMENSION = "DIMENSION";
    public const DATE      = "DATE";
    public const JSON      = "JSON";


    public static function all()
    {
        $rSelf = new ReflectionClass(__CLASS__);

        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
