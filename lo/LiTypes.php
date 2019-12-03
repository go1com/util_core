<?php

namespace go1\util\lo;

use ReflectionClass;

class LiTypes
{
    const ACTIVITY    = 'activities';
    const ASSIGNMENT  = 'assignment';
    const ATTENDANCE  = 'attendance';
    const AUDIO       = 'audio';
    const DOCUMENT    = 'document';
    const EVENT       = 'event';
    const H5P         = 'h5p';
    /**
     * @deprecated use the LINK type instead
     */
    const IFRAME      = 'iframe';
    const INTEGRATION = 'integration';
    const INTERACTIVE = 'interactive';
    const LINK        = 'link';
    const LTI         = 'lti';
    const MANUAL      = 'manual';
    const QUESTION    = 'question';
    const QUIZ        = 'quiz';
    /**
     * @deprecated use the TEXT type instead
     */
    const RESOURCE    = 'resource';
    const TEXT        = 'text';
    const VIDEO       = 'video';
    const WORKSHOP    = 'workshop';

    const COMPLEX     = ['assignment', 'event', 'h5p', 'interactive', 'quiz', 'lti'];

    const PRIVATE_PROPERTIES = [
        self::DOCUMENT    => ['path'],
        self::H5P         => ['path'],
        self::INTERACTIVE => ['url'],
    ];

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
