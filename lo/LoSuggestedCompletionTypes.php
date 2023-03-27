<?php

namespace go1\util\lo;

class LoSuggestedCompletionTypes
{
    public const DUE_DATE          = 1;
    public const E_DURATION        = 2;
    public const E_PARENT_DURATION = 3;
    public const COURSE_ENROLMENT  = 4;

    public const ALL = [self::DUE_DATE, self::E_DURATION, self::E_PARENT_DURATION, self::COURSE_ENROLMENT];
}
