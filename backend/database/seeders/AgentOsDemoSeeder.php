<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgentOsDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            /*
             * Demo users
             */
            $owner = User::query()->updateOrCreate(
                [
                    'email' => 'owner@agentos.test',
                ],
                [
                    'name' => 'AgentOS Owner',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $admin = User::query()->updateOrCreate(
                [
                    'email' => 'admin@agentos.test',
                ],
                [
                    'name' => 'AgentOS Admin',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $member = User::query()->updateOrCreate(
                [
                    'email' => 'member@agentos.test',
                ],
                [
                    'name' => 'AgentOS Member',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $viewer = User::query()->updateOrCreate(
                [
                    'email' => 'viewer@agentos.test',
                ],
                [
                    'name' => 'AgentOS Viewer',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            /*
             * Organization pertama
             */
            $anamDigital = Organization::withTrashed()->updateOrCreate(
                [
                    'slug' => 'anam-digital',
                ],
                [
                    'name' => 'Anam Digital',
                    'timezone' => 'Asia/Jakarta',
                    'locale' => 'id',
                    'status' => 'active',
                    'created_by' => $owner->id,
                    'deleted_at' => null,
                ]
            );

            /*
             * Organization kedua
             */
            $agentOsLabs = Organization::withTrashed()->updateOrCreate(
                [
                    'slug' => 'agentos-labs',
                ],
                [
                    'name' => 'AgentOS Labs',
                    'timezone' => 'Asia/Jakarta',
                    'locale' => 'id',
                    'status' => 'active',
                    'created_by' => $owner->id,
                    'deleted_at' => null,
                ]
            );

            /*
             * Membership Anam Digital
             */
            $this->createMembership(
                organizationId: $anamDigital->id,
                userId: $owner->id,
                role: OrganizationRole::Owner,
                invitedBy: null
            );

            $this->createMembership(
                organizationId: $anamDigital->id,
                userId: $admin->id,
                role: OrganizationRole::Admin,
                invitedBy: $owner->id
            );

            $this->createMembership(
                organizationId: $anamDigital->id,
                userId: $member->id,
                role: OrganizationRole::Member,
                invitedBy: $owner->id
            );

            $this->createMembership(
                organizationId: $anamDigital->id,
                userId: $viewer->id,
                role: OrganizationRole::Viewer,
                invitedBy: $owner->id
            );

            /*
             * Membership AgentOS Labs
             */
            $this->createMembership(
                organizationId: $agentOsLabs->id,
                userId: $owner->id,
                role: OrganizationRole::Owner,
                invitedBy: null
            );

            $this->createMembership(
                organizationId: $agentOsLabs->id,
                userId: $member->id,
                role: OrganizationRole::Admin,
                invitedBy: $owner->id
            );
        });
    }

    private function createMembership(
        int $organizationId,
        int $userId,
        OrganizationRole $role,
        ?int $invitedBy
    ): void {
        OrganizationMember::query()->updateOrCreate(
            [
                'organization_id' => $organizationId,
                'user_id' => $userId,
            ],
            [
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
                'invited_by' => $invitedBy,
            ]
        );
    }
}