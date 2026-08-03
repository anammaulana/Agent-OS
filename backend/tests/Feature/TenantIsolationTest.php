<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_organization(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $organizationA = $this->createOrganizationFor(
            $userA,
            'Tenant A',
            'tenant-a'
        );

        $organizationB = $this->createOrganizationFor(
            $userB,
            'Tenant B',
            'tenant-b'
        );

        $tokenA = $userA
            ->createToken('test')
            ->plainTextToken;

        $this
            ->withToken($tokenA)
            ->withHeader(
                'X-Organization-ID',
                (string) $organizationB->id
            )
            ->getJson('/api/organization')
            ->assertNotFound()
            ->assertJsonPath(
                'message',
                'Organization tidak ditemukan.'
            );
    }

    public function test_request_requires_organization_header(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this
            ->withToken($token)
            ->getJson('/api/organization')
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Header X-Organization-ID wajib diisi.'
            );
    }

    public function test_member_cannot_update_organization(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $organization = $this->createOrganizationFor(
            $owner,
            'Secure Tenant',
            'secure-tenant'
        );

        $organization->memberships()->create([
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $token = $member
            ->createToken('test')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->withHeader(
                'X-Organization-ID',
                (string) $organization->id
            )
            ->putJson('/api/organization', [
                'name' => 'Unauthorized Change',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('organizations', [
            'id' => $organization->id,
            'name' => 'Unauthorized Change',
        ]);
    }

    private function createOrganizationFor(
        User $user,
        string $name,
        string $slug
    ): Organization {
        $organization = Organization::create([
            'name' => $name,
            'slug' => $slug,
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $organization->memberships()->create([
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $organization;
    }
}