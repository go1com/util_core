<?php

namespace go1\util\portal;

class ChannelType extends ConstantContainer
{
    protected static $name = 'channel';

    protected static $customFormats = [
        self::SALES => 'SDR / Account Exec',
        self::DIRECT => 'Direct or Inbound',
    ];

    public const INTERNAL             = 'internal';
    public const REFERRAL_PARTNER     = 'referral_partner';
    public const DISTRIBUTION_PARTNER = 'distribution_partner';
    public const SALES                = 'sales';
    public const EXISTING_CUSTOMER    = 'existing_customer';
    public const DIRECT               = 'direct';
    public const PLATFORM_PARTNER     = 'platform_partner';
    public const PORTAL_LAUNCHER      = 'portal_launcher';
    public const CONTENT_PARTNER      = 'content_partner';
}
