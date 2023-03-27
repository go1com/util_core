<?php

namespace go1\util\lo\event_publishing;

class EventAttendanceDelete extends EventAttendanceUpdate
{
    public const ROUTING_KEY = 'event.attendance.delete';
}
