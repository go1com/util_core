<?php

namespace go1\util\lo;

use ReflectionClass;

class LiTypes
{
    public const ASSIGNMENT  = 'assignment';
    public const AUDIO       = 'audio';
    public const DOCUMENT    = 'document';
    public const EVENT       = 'event';
    public const INTEGRATION = 'integration';
    public const INTERACTIVE = 'interactive';
    public const MANUAL      = 'manual';
    public const LINK        = 'link';
    public const LTI         = 'lti';
    public const QUESTION    = 'question';
    public const QUIZ        = 'quiz';
    public const TEXT        = 'text';
    public const VIDEO       = 'video';
    public const COMPLEX     = ['assignment', 'h5p', 'interactive', 'quiz', 'lti', 'event'];

    // deprecated LI types
    /**
     * @deprecated use the ASSIGNMENT type instead
     */
    public const ACTIVITY    = 'activities';

    /**
     * @deprecated
     */
    public const H5P         = 'h5p';

    /**
     * @deprecated use the EVENT type instead
     */
    public const ATTENDANCE  = 'attendance';

    /**
     * @deprecated use the LINK type instead
     */
    public const IFRAME      = 'iframe';

    /**
     * @deprecated use the TEXT or DOCUMENT type instead
     */
    public const RESOURCE    = 'resource';

    /**
     * @deprecated use the EVENT type instead
     */
    public const WORKSHOP    = 'workshop';

    public const PRIVATE_PROPERTIES = [
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
