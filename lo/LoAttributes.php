<?php

namespace go1\util\lo;

use ReflectionClass;

class LoAttributes
{
    public const MOBILE_OPTIMISED    = 1;
    public const WCAG                = 2;  // Web Content Accessibility Guidelines compatible
    public const ASSESSABLE          = 3;
    public const AVAILABILITY        = 4;  // marketplace
    /**
     * @deprecated use the REGION_RESTRICTIONS type instead
     */
    public const REGION_RESTRICTION            = 5;
    public const TOPICS                        = 6;
    public const REGION_RESTRICTIONS           = 7;
    public const LEARNING_OUTCOMES             = 8;
    public const PROVIDER                      = 9;
    public const INTERNAL_QA_RATING            = 10;
    public const DOWNLOAD_SPEED                = 11;
    public const AUDIO_VISUAL_DESIGN           = 12;
    public const PRESENTATION_OF_CONTENT       = 13;
    public const STRUCTURE_NAVIGATION          = 14;
    public const INTEGRATION                   = 15;
    public const INTEGRATION_URL               = 16;
    public const INDUSTRY                      = 17;
    public const COMPANY_SIZE                  = 18;
    public const YEAR_CREATED                  = 19;
    public const FEATURED_STATUS               = 20;
    public const FEATURED_LOCALE               = 21;
    public const FEATURED_TIMESTAMP            = 22;
    public const ENTRY_LEVEL                   = 23;
    public const LOCALE                        = 24;
    public const REGION_RELEVANCE              = 25;
    public const CHECK_URL                     = 26;
    public const ROLES                         = 27;
    public const SKILLS                        = 28;
    public const SUBSCRIPTION_RENEWAL_DATE     = 29;
    public const PLAYLIST_TYPE                 = 30;
    public const PLAYBACK_TARGET               = 31;
    public const STREAMABLE                    = 32;
    public const CURATED_BY                    = 33; // use this to surface content curation tool and also in recommendations, could be used as a signal displayed on lo cards. Eg: curated_by = "go1"
    public const FEATURED_BY                   = 34; // for featured playlists, combine use with featured_status, Eg: featured_status = true & featured_by = "go1"

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
            self::ENTRY_LEVEL               => 'entry_level',
            self::LOCALE                    => 'locale',
            self::REGION_RELEVANCE          => 'region_relevance',
            self::CHECK_URL                 => 'check_url',
            self::ROLES                     => 'roles',
            self::SKILLS                    => 'skills',
            self::SUBSCRIPTION_RENEWAL_DATE => 'subscription_renewal_date',
            self::PLAYLIST_TYPE             => 'playlist_type',
            self::PLAYBACK_TARGET           => 'playback_target',
            self::STREAMABLE                => 'streamable',
            self::CURATED_BY                => 'curated_by',
            self::FEATURED_BY               => 'featured_by',
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
