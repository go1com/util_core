<?php

namespace go1\util\lo;

class LoTypes
{
    public const COURSE          = 'course';
    public const MODULE          = 'module';
    public const AWARD           = 'award';
    public const GROUP           = 'group';
    public const ACHIEVEMENT     = 'achievement';
    public const PLAYLIST        = 'playlist';

    // deprecated LO types
    /**
     * @deprecated
     */
    public const LEARNING_PATHWAY = 'learning_pathway';

    public static function all()
    {
        return [self::LEARNING_PATHWAY, self::COURSE, self::MODULE, self::AWARD, self::GROUP, self::ACHIEVEMENT, self::PLAYLIST];
    }

    public static function allTheThing()
    {
        return array_merge(self::all(), LiTypes::all());
    }
}
