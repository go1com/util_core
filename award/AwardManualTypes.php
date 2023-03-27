<?php

namespace go1\util\award;

use ReflectionClass;

class AwardManualTypes
{
    public const BOOK       = 'book';
    public const ARTICLE    = 'article';
    public const JOURNAL    = 'journal';
    public const F2F        = 'face to face';
    public const ONLINE     = 'online';
    public const OTHER      = 'other';
    public const ON_THE_JOB = 'on the job';

    public static function all()
    {
        $rClass = new ReflectionClass(static::class);

        return $rClass->getConstants();
    }
}
