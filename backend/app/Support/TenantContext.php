<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\OrganizationMember;
use LogicException;

class TenantContext
{
    private ?Organization $organization = null;

    private ?OrganizationMember $membership = null;

    public function set(
        Organization $organization,
        OrganizationMember $membership
    ): void {
        $this->organization = $organization;
        $this->membership = $membership;
    }

    public function organization(): Organization
    {
        if (!$this->organization) {
            throw new LogicException(
                'Organization context belum tersedia.'
            );
        }

        return $this->organization;
    }

    public function membership(): OrganizationMember
    {
        if (!$this->membership) {
            throw new LogicException(
                'Membership context belum tersedia.'
            );
        }

        return $this->membership;
    }

    public function organizationId(): int
    {
        return (int) $this->organization()->getKey();
    }

    public function hasOrganization(): bool
    {
        return $this->organization !== null;
    }
}