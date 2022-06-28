<?php

namespace go1\util\es;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.2/mapping-types.html
 */
class EventSchema
{
    const INDEX = Schema::INDEX . '_event';

    const O_EVENT = 'event';

    const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    const BODY = [
        'mappings' => self::EVENT_MAPPING,
    ];

    const EVENT_PROPERTIES = [
        'id'                       => ['type' => Schema::T_KEYWORD],
        'lo_id'                    => ['type' => Schema::T_INT],
        'title'                    => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'start'                    => ['type' => Schema::T_DATE],
        'end'                      => ['type' => Schema::T_DATE],
        'timezone'                 => ['type' => Schema::T_KEYWORD],
        'seats'                    => ['type' => Schema::T_INT], # Or attendee_limit
        'available_seats'          => ['type' => Schema::T_INT],
        'country'                  => ['type' => Schema::T_KEYWORD],
        'country_name'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'administrative_area'      => ['type' => Schema::T_KEYWORD],
        'administrative_area_name' => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'sub_administrative_area'  => ['type' => Schema::T_KEYWORD],
        'locality'                 => ['type' => Schema::T_KEYWORD],
        'dependent_locality'       => ['type' => Schema::T_KEYWORD],
        'thoroughfare'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'premise'                  => ['type' => Schema::T_KEYWORD],
        'sub_premise'              => ['type' => Schema::T_KEYWORD],
        'organisation_name'        => ['type' => Schema::T_KEYWORD],
        'name_line'                => ['type' => Schema::T_KEYWORD],
        'postal_code'              => ['type' => Schema::T_KEYWORD],
        'location_name'            => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'module_title'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
        'instructor_ids'           => ['type' => Schema::T_INT],
        'instructors'              => [
            'type'       => Schema::T_NESTED,
            'properties' => Schema::INSTRUCTOR_PROPERTIES,
        ],
        'coordinate'               => ['type' => Schema::T_GEO_POINT],
    ];

    const EVENT_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => self::EVENT_PROPERTIES + [
                'parent'   => [
                    'properties' => Schema::LO_MAPPING['properties'],
                ],
                'metadata' => [
                    'properties' => [
                        'instance_id' => ['type' => Schema::T_INT],
                        'updated_at'  => ['type' => Schema::T_INT],
                    ],
                ],
            ],
    ];
}
