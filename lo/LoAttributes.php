<?php

namespace go1\util\lo;

use ReflectionClass;

class LoAttributes
{
    const MOBILE_OPTIMISED    = 1;
    const WCAG                = 2;  // Web Content Accessibility Guidelines compatible
    const ASSESSABLE          = 3;
    const AVAILABILITY        = 4;  // marketplace
    /**
     * @deprecated use the REGION_RESTRICTIONS type instead
     */
    const REGION_RESTRICTION            = 5;
    const TOPICS                        = 6;
    const REGION_RESTRICTIONS           = 7;
    const LEARNING_OUTCOMES             = 8;
    const PROVIDER                      = 9;
    const INTERNAL_QA_RATING            = 10;
    const DOWNLOAD_SPEED                = 11;
    const AUDIO_VISUAL_DESIGN           = 12;
    const PRESENTATION_OF_CONTENT       = 13;
    const STRUCTURE_NAVIGATION          = 14;
    const INTEGRATION                   = 15;
    const INTEGRATION_URL               = 16;
    const INDUSTRY                      = 17;
    const COMPANY_SIZE                  = 18;
    const YEAR_CREATED                  = 19;
    const FEATURED_STATUS               = 20;
    const FEATURED_LOCALE               = 21;
    const FEATURED_TIMESTAMP            = 22;

    public static function machineName(int $attribute): ?string
    {
        $map = [
            self::MOBILE_OPTIMISED          => 'mobile_optimised',
            self::WCAG                      => 'wcag',
            self::ASSESSABLE                => 'assessable',
            self::AVAILABILITY              => 'availability',
            self::REGION_RESTRICTION        => 'region_restriction',
            self::REGION_RESTRICTIONS       => 'region_restrictions',
            self::TOPICS                    => 'topics',
            self::LEARNING_OUTCOMES         => 'learning_outcomes',
            self::PROVIDER                  => 'provider',
            self::INTERNAL_QA_RATING        => 'internal_qa_rating',
            self::DOWNLOAD_SPEED            => 'download_speed',
            self::AUDIO_VISUAL_DESIGN       => 'audio_visual_design',
            self::PRESENTATION_OF_CONTENT   => 'presentation_of_content',
            self::STRUCTURE_NAVIGATION      => 'structure_navigation',
            self::INTEGRATION               => 'integration',
            self::INTEGRATION_URL           => 'integration_url',
            self::INDUSTRY                  => 'industry',
            self::COMPANY_SIZE              => 'company_size',
            self::YEAR_CREATED              => 'year_created',
            self::FEATURED_STATUS           => 'featured_status',
            self::FEATURED_LOCALE           => 'featured_locale',
            self::FEATURED_TIMESTAMP        => 'featured_timestamp',
        ];

        return $map[$attribute] ?? null;
    }

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
