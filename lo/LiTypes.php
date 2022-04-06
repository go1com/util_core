<?php

namespace go1\util\lo;

use ReflectionClass;

class LiTypes
{
    const ASSIGNMENT  = 'assignment';
    const AUDIO       = 'audio';
    const DOCUMENT    = 'document';
    const EVENT       = 'event';
    const H5P         = 'h5p';
    const INTEGRATION = 'integration';
    const INTERACTIVE = 'interactive';
    const LINK        = 'link';
    const LTI         = 'lti';
    const QUESTION    = 'question';
    const QUIZ        = 'quiz';
    const TEXT        = 'text';
    const VIDEO       = 'video';
    const COMPLEX     = ['assignment', 'h5p', 'interactive', 'quiz', 'lti', 'event'];

    // deprecated LI types
    /**
     * @deprecated use the ASSIGNMENT type instead
     */
    const ACTIVITY    = 'activities';

    /**
     * @deprecated use the EVENT type instead
     */
    const ATTENDANCE  = 'attendance';

    /**
     * @deprecated use the LINK type instead
     */
    const IFRAME      = 'iframe';

    /**
     * @deprecated
     */
    const MANUAL      = 'manual';

    /**
     * @deprecated use the TEXT or DOCUMENT type instead
     */
    const RESOURCE    = 'resource';

    /**
     * @deprecated use the EVENT type instead
     */
    const WORKSHOP    = 'workshop';

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
