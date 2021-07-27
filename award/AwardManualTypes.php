<?php

namespace go1\util\award;

use ReflectionClass;

class AwardManualTypes
{
    const BOOK       = 'book';
    const ARTICLE    = 'article';
    const JOURNAL    = 'journal';
    const F2F        = 'face to face';
    const ONLINE     = 'online';
    const OTHER      = 'other';
    const ON_THE_JOB = 'on the job';

    public static function all()
    {
        $rClass = new ReflectionClass(static::class);

        return $rClass->getConstants();
    }
}
