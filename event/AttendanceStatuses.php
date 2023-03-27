<?php

namespace go1\util\event;

class AttendanceStatuses
{
    public const ATTENDED     = 'attended';
    public const NOT_ATTENDED = 'not-attended';
    public const ATTENDING    = 'attending';
    public const PENDING      = 'pending';

    public const I_PENDING           = 0;
    public const I_ATTENDING         = 1;
    public const I_ATTENDED          = 2;
    public const I_NOT_ATTENDED      = 3;
}
