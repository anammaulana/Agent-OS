<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function canManageOrganization(): bool
    {
        return in_array($this, [
            self::Owner,
            self::Admin,
        ], true);
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [
            self::Owner,
            self::Admin,
        ], true);
    }
}
