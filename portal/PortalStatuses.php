<?php

namespace go1\util\portal;

class PortalStatuses
{
    public const ONBOARDING = -100;
    public const DELETED    = -2;
    public const DISABLED   = -1;
    public const QUEUED     = 0;
    public const ENABLED    = 1;

    public const TIERS  = ['Unclassified', 'Trial', 'Paid', 'Free', 'Test', 'Inactive'];
    public const STAGES = ['Pre-onboarding', 'Onboarding', 'Established', 'Advocate', 'Inactive'];
}
