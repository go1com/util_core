<?php

namespace go1\util\lo;

class LoTypes
{
    const LEANING_PATHWAY = 'learning_pathway';
    const COURSE          = 'course';
    const MODULE          = 'module';
    const AWARD           = 'award';
    const GROUP           = 'group';
    const ACHIEVEMENT     = 'achievement';
    const MANUAL          = 'manual';

    public static function all()
    {
        return [self::LEANING_PATHWAY, self::COURSE, self::MODULE, self::AWARD, self::GROUP, self::ACHIEVEMENT, self::MANUAL];
    }

    public static function allTheThing()
    {
        return array_merge(self::all(), LiTypes::all());
    }
}
