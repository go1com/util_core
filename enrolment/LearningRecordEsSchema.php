<?php

namespace go1\util\enrolment;

use go1\util\customer\CustomerEsSchema;
use go1\util\es\Schema;

class LearningRecordEsSchema
{
    const INDEX = ES_INDEX . '_learning_record';

    public static function indexSchema(): array
    {
        return [
            'settings' => [
                'number_of_shards'                 => getenv('ES_SCHEMA_NUMBER_OF_SHARDS') ?: 5,
                'number_of_replicas'               => getenv('ES_SCHEMA_NUMBER_OF_REPLICAS') ?: 1,
                'index.mapping.total_fields.limit' => getenv('ES_SCHEMA_LIMIT_TOTAL_FIELDS') ?: 5000,
            ],
            'mappings' => self::mapping(),
        ];
    }

    private static function mapping()
    {
        $mapping = [
            Schema::O_ENROLMENT          => Schema::ENROLMENT_MAPPING,
            Schema::O_ENROLMENT_REVISION => Schema::ENROLMENT_MAPPING_REVISION,
            Schema::O_PLAN               => Schema::PLAN_MAPPING,
        ];

        # don't use dynamic mapping for custom fields.
        unset($mapping[Schema::O_ENROLMENT]['dynamic_templates']);
        $mapping[Schema::O_ENROLMENT]['properties']['account']['properties'] += CustomerEsSchema::customFieldMapping();

        return $mapping;
    }
}
