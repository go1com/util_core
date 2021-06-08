<?php

namespace go1\util\portal;

class PartnerConfigurationsInheritance
{
    public const PARTNER_PRESCRIBED_CONFIG_KEY = 'partner_prescribed_configurations';

    protected const DEFAULT_MAPPING = [
        'signup' => [
            'configuration.signup_tagline',
            'configuration.signup_secondary_tagline',
        ],
        'login' => [
            'configuration.login_tagline',
            'configuration.login_secondary_tagline',
        ],
        'logo' => [
            'files.logo',
        ],
        'featured_image' => [
            'files.login_background',
        ],
        'portal_color' => [
            'theme.primary',
        ],
        'dashboard' => [
            'files.feature_image',
            'files.feature_image_sizing_type',
            'files.dashboard_icon',
            'configuration.welcome',
        ],
        'certificate' => [
            'configuration.signature_title',
            'configuration.signature_name',
            'configuration.signature_image',
        ]
    ];

    /** @var array<string, string[]>  */
    protected array $mapping = [];

    /**
     * @param array<string, string[]> $mapping
     */
    public function __construct(array $mapping = null)
    {
        $this->mapping = $mapping ?? self::DEFAULT_MAPPING;
    }

    /**
     * @return string[]
     */
    public function getGroups(): array
    {
        return array_keys($this->mapping);
    }

    /**
     * @return string[]
     */
    public function getConfigurationsForGroup(string $group): array
    {
        return $this->mapping[$group] ?? [];
    }
}
