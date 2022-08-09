<?php

namespace go1\util\es;

use go1\util\event\AttendanceStatuses;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.2/mapping-types.html
 */
class EsEventAttendanceSchema
{
    const INDEX = Schema::INDEX . '_event_attendance';

    const O_EVENT_ATTENDANCE = 'event_attendance';

    const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    const BODY = [
        'mappings' => self::EVENT_ATTENDANCE_MAPPING,
    ];

    const EVENT_ATTENDANCE_PROPERTIES = [
        'id'           => ['type' => Schema::T_KEYWORD],
        'user_id'      => ['type' => Schema::T_INT],
        'lo_id'        => ['type' => Schema::T_INT],
        'enrolment_id' => ['type' => Schema::T_INT],
        'event_id'     => ['type' => Schema::T_INT],
        'portal_id'    => ['type' => Schema::T_INT],
        'profile_id'   => ['type' => Schema::T_INT],
        'start_at'     => ['type' => Schema::T_DATE],
        'end_at'       => ['type' => Schema::T_DATE],
        'status'       => ['type' => Schema::T_SHORT],
        'result'       => ['type' => Schema::T_INT],
        'pass'         => ['type' => Schema::T_INT],
        'timestamp'    => ['type' => Schema::T_DATE],
        'progress'     => [
            'properties' => [
                AttendanceStatuses::ATTENDED     => ['type' => Schema::T_INT],
                AttendanceStatuses::NOT_ATTENDED => ['type' => Schema::T_INT],
                AttendanceStatuses::ATTENDING    => ['type' => Schema::T_INT],
                AttendanceStatuses::PENDING      => ['type' => Schema::T_INT],
            ],
        ],
        'account'      => [
            'properties' => Schema::ACCOUNT_LITE_PROPERTIES,
        ],
        'event'        => [
            'properties' => EsEventSchema::EVENT_PROPERTIES,
        ],
    ];

    const EVENT_ATTENDANCE_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => self::EVENT_ATTENDANCE_PROPERTIES + [
                'metadata' => [
                    'properties' => [
                        'instance_id' => ['type' => Schema::T_INT],
                        'updated_at'  => ['type' => Schema::T_INT],
                    ],
                ],
            ],
    ];

    public static function indexEsSchema(): array
    {
        return [
            'settings' => [
                'number_of_shards'                 => getenv('ES_SCHEMA_NUMBER_OF_SHARDS') ?: 2,
                'number_of_replicas'               => getenv('ES_SCHEMA_NUMBER_OF_REPLICAS') ?: 1,
                'index.mapping.total_fields.limit' => getenv('ES_SCHEMA_LIMIT_TOTAL_FIELDS') ?: 20000,
            ],
            'mappings' => EsEventAttendanceSchema::EVENT_ATTENDANCE_MAPPING,
        ];
    }
}
