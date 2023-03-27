<?php

namespace go1\util\es;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.2/mapping-types.html
 */
class TermSchema
{
    public const INDEX = Schema::INDEX . '_term';

    public const O_TERM = 'term';

    public const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    public const BODY = [
        'mappings' => self::TERM_MAPPING,
    ];

    public const TERM_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'term'  => [
                'type'                         => Schema::T_COMPLETION,
                'analyzer'                     => Schema::A_SIMPLE,
                'preserve_separators'          => true,
                'preserve_position_increments' => true,
                'max_input_length'             => Schema::MAX_INPUT_LENGTH,
                'contexts'                     => [
                    [
                        'name' => 'topic',
                        'type' => Schema::T_COMPLETION_CATEGORY,
                        'path' => 'topic',
                    ],
                ],
            ],
            'topic' => [
                'type' => Schema::T_TEXT,
            ],
        ],
    ];
}
