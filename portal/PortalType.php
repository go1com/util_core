<?php

namespace go1\util\portal;

class PortalType extends ConstantContainer
{
    protected static $name = 'portal';

    protected static $customFormats = [
        self::JSE_CUSTOMER => 'JSE Customer'
    ];

    public const CONTENT_PARTNER      = 'content_partner';
    public const DISTRIBUTION_PARTNER = 'distribution_partner';
    public const INTERNAL             = 'internal';
    public const CUSTOMER             = 'customer';
    public const COMPLISPACE          = 'complispace';
    public const JSE_CUSTOMER         = 'jse_customer';
    public const TOTARA_CUSTOMER      = 'totara_customer';
    public const TEAM                 = 'team';
    public const REPORTABLE_CUSTOMER  = 'reportable_customer';
}
