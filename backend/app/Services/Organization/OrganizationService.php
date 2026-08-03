<?php

namespace App\Services\Organization;

use App\Contracts\AuditLogger;
use App\Enums\OrganizationRole;
use App\Exceptions\DomainException;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationService
{
    public function __construct(private readonly TenantContext $tenantContext, private readonly AuditLogger $auditLogger) {}
    public function listForUser(User $user): Collection
    {
        return $user->organizations()->wherePivot('status', 'active')->where('organizations.status', 'active')->orderBy('organizations.name')->get();
    }
    public function create(User $actor, array $data): Organization
    {
        $organization = DB::transaction(function () use ($actor, $data): Organization {
            $organization = Organization::query()->create(['name' => $data['name'], 'slug' => $this->generateUniqueSlug($data['name']), 'timezone' => $data['timezone'] ?? 'Asia/Jakarta', 'locale' => $data['locale'] ?? 'id', 'status' => 'active', 'created_by' => $actor->id,]);
            $organization->memberships()->create(['user_id' => $actor->id, 'role' => OrganizationRole::Owner, 'status' => 'active', 'joined_at' => now(),]);
            return $organization;
        });
        $this->auditLogger->record(action: 'organization.created', resource: $organization, description: 'Organization dibuat.', newValues: ['name' => $organization->name, 'slug' => $organization->slug, 'timezone' => $organization->timezone, 'locale' => $organization->locale,], organizationId: $organization->id, actorId: $actor->id);
        return $organization;
    }
    public function update(array $data): Organization
    {
        $organization = $this->tenantContext->organization();
        $oldValues = $organization->only(['name', 'slug', 'timezone', 'locale', 'status',]);
        $organization->update($data);
        $organization = $organization->fresh();
        $this->auditLogger->record(action: 'organization.updated', resource: $organization, description: 'Organization diperbarui.', oldValues: $oldValues, newValues: $organization->only(['name', 'slug', 'timezone', 'locale', 'status',]));
        return $organization;
    }
    public function deactivate(): void
    {
        $organization = $this->tenantContext->organization();
        DB::transaction(function () use ($organization): void {
            $organization->update(['status' => 'inactive',]);
            $organization->delete();
        });
        $this->auditLogger->record(action: 'organization.deactivated', resource: $organization, description: 'Organization dinonaktifkan.', oldValues: ['status' => 'active',], newValues: ['status' => 'inactive',], organizationId: $organization->id);
    }
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'organization';
        }
        $slug = $baseSlug;
        $counter = 1;
        while (Organization::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
