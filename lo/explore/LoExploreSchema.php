<?php

namespace go1\util\lo\explore;

use go1\util\es\Schema;

class LoExploreSchema
{
    const BODY = [
        'mappings' => self::MAPPING,
    ];

    const MAPPING = [
        Schema::O_LO         => self::LO_MAPPING,
        Schema::O_ENROLMENT  => self::ENROLMENT_MAPPING,
        Schema::O_GROUP_ITEM => self::GROUP_ITEM_MAPPING,
    ];

    const LO_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'              => ['type' => Schema::T_KEYWORD],
            'type'            => ['type' => Schema::T_KEYWORD],
            'origin_id'       => ['type' => Schema::T_INT],
            'private'         => ['type' => Schema::T_INT],
            'published'       => ['type' => Schema::T_INT],
            'marketplace'     => ['type' => Schema::T_INT],
            'sharing'         => ['type' => Schema::T_SHORT],
            'portal_id'       => ['type' => Schema::T_INT],
            'portal_name'     => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
            'language'        => ['type' => Schema::T_KEYWORD],
            'locale'          => ['type' => Schema::T_KEYWORD],
            'title'           => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
            'description'     => ['type' => Schema::T_TEXT],
            'tags'            => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
            'custom_tags'     => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
            'pricing'         => [
                'properties' => [
                    'currency' => ['type' => Schema::T_KEYWORD],
                    'price'    => ['type' => Schema::T_DOUBLE],
                    'total'    => ['type' => Schema::T_DOUBLE],
                ],
            ],
            'assessors'       => ['type' => Schema::T_INT],
            'collections'     => ['type' => Schema::T_INT],
            'group'           => [
                'properties' => [
                    'content' => ['type' => Schema::T_INT],
                ],
            ],
            'allow_enrolment' => ['type' => Schema::T_INT],
            'totalEnrolment'  => ['type' => Schema::T_INT],
            'created'         => ['type' => Schema::T_DATE],
            'updated'         => ['type' => Schema::T_DATE],
            'authors'         => ['type' => Schema::T_INT],
            'data'            => [
                'properties' => [
                    'single_li' => ['type' => Schema::T_SHORT],
                ],
            ],
            'locations'       => [
                'type'       => Schema::T_NESTED,
                'properties' => [
                    'id'                       => ['type' => Schema::T_KEYWORD],
                    'country'                  => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'country_name'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'administrative_area'      => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'administrative_area_name' => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'locality'                 => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'thoroughfare'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'coordinate'               => ['type' => Schema::T_GEO_POINT],
                ],
            ],
            'events'          => [
                'type'       => Schema::T_NESTED,
                'properties' => [
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
                    'locality'                 => ['type' => Schema::T_KEYWORD],
                    'dependent_locality'       => ['type' => Schema::T_KEYWORD],
                    'thoroughfare'             => ['type' => Schema::T_KEYWORD] + Schema::ANALYZED,
                    'instructor_ids'           => ['type' => Schema::T_INT],
                ],
            ],
            'vote'            => [
                'properties' => [
                    'rank' => ['type' => Schema::T_INT],
                ],
            ],
            'metadata'        => [
                'properties' => [
                    'portal_id'  => ['type' => Schema::T_INT],
                    'updated_at' => ['type' => Schema::T_INT],
                ],
            ],
        ],
    ];

    const ENROLMENT_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'         => ['type' => Schema::T_KEYWORD],
            'type'       => ['type' => Schema::T_KEYWORD],
            'account_id' => ['type' => Schema::T_INT],
            'status'     => ['type' => Schema::T_SHORT],
            'pass'       => ['type' => Schema::T_INT],
            'portal_id'  => ['type' => Schema::T_INT],
            'metadata'   => [
                'properties' => [
                    'portal_id'  => ['type' => Schema::T_INT],
                    'updated_at' => ['type' => Schema::T_INT],
                ],
            ],
        ],
    ];

    const GROUP_ITEM_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => Schema::T_KEYWORD],
            'group_id'    => ['type' => Schema::T_INT],
            'entity_type' => ['type' => Schema::T_KEYWORD],
            'entity_id'   => ['type' => Schema::T_INT],
            'portal_id'   => ['type' => Schema::T_INT],
            'metadata'    => [
                'properties' => [
                    'portal_id'  => ['type' => Schema::T_INT],
                    'updated_at' => ['type' => Schema::T_INT],
                ],
            ],
        ],
    ];
}
