<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_organization(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/organizations', [
                'name' => 'Anam Digital',
                'timezone' => 'Asia/Jakarta',
                'locale' => 'id',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Anam Digital');

        $this->assertDatabaseHas('organizations', [
            'name' => 'Anam Digital',
            'created_by' => $user->id,
        ]);

        $organization = Organization::firstOrFail();

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_create_organization(): void
    {
        $this
            ->postJson('/api/organizations', [
                'name' => 'Unauthorized Organization',
            ])
            ->assertUnauthorized();
    }

    public function test_member_can_list_only_joined_organizations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $owned = Organization::create([
            'name' => 'Owned',
            'slug' => 'owned',
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $hidden = Organization::create([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'timezone' => 'Asia/Jakarta',
            'locale' => 'id',
            'status' => 'active',
            'created_by' => $otherUser->id,
        ]);

        $owned->memberships()->create([
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this
            ->withToken($token)
            ->getJson('/api/organizations')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Owned'])
            ->assertJsonMissing(['name' => 'Hidden']);
    }
}