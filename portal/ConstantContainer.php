<?php

namespace go1\util\portal;

abstract class ConstantContainer
{
    protected static $name = '';

    protected static $customFormats = [];

    public static function all(): array
    {
        $reflectedClass = new \ReflectionClass(static::class);
        $constants = $reflectedClass->getConstants();
        $types = array_values($constants);
        return $types;
    }

    public static function toString(string $type): string
    {
        if (!in_array($type, static::all(), true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s type: %s',
                static::$name,
                $type
            ));
        }

        //Special formatting
        if (isset(static::$customFormats[$type])) {
            return static::$customFormats[$type];
        }

        //Default formatting
        return implode(' ', array_map('ucwords', explode('_', $type)));
    }
}
