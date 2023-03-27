<?php

namespace go1\util\user;

class UserStatus
{
    public const INACTIVE = 0;
    public const ACTIVE   = 1;
    // @deprecated by no longer use virtual account
    public const VIRTUAL  = 2; // Linked to user by HAS_ACCOUNT_VIRTUAL
}
