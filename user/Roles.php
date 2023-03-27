<?php

namespace go1\util\user;

class Roles
{
    public const ROOT          = 'Admin on #Accounts';
    public const ADMIN         = 'administrator';
    public const ADMIN_CONTENT = 'content administrator';
    public const DEVELOPER     = 'developer';
    public const AUTHENTICATED = 'authenticated user';
    public const STUDENT       = 'Student';
    public const TUTOR         = 'tutor';
    public const ASSESSOR      = 'tutor';
    public const MANAGER       = 'manager';
    public const TAM           = 'training account manager';
    public const ANONYMOUS     = 'anonymous';

    public const ACCOUNTS_ROLES     = [self::ROOT, self::DEVELOPER, self::AUTHENTICATED, self::TAM];
    public const PORTAL_ROLES       = [self::ANONYMOUS, self::AUTHENTICATED, self::ADMIN, self::ADMIN_CONTENT, self::STUDENT, self::TUTOR, self::MANAGER];
    public const PORTAL_ADMIN_ROLES = [self::ADMIN, self::ADMIN_CONTENT, self::MANAGER]; # Roles can access portal admin area.

    public const NAMES = [
        self::ADMIN         => 'Administrator',
        self::STUDENT       => 'Learner',
        self::ASSESSOR      => 'Assessor',
        self::MANAGER       => 'Manager',
        self::ADMIN_CONTENT => 'Content administrator',
    ];

    public static function getRoleByName(string $roleName)
    {
        if ($roleName == self::STUDENT) {
            return self::STUDENT;
        }

        foreach (self::NAMES as $role => $name) {
            if ($name == $roleName) {
                return $role;
            }
        }

        return false;
    }
}
