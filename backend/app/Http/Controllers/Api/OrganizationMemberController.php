<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationMemberRequest;
use App\Http\Requests\UpdateOrganizationMemberRequest;
use App\Http\Resources\OrganizationMemberResource;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationMemberController extends Controller
{
    public function index(TenantContext $tenantContext)
    {
        $members = OrganizationMember::query()
            ->with('user')
            ->where(
                'organization_id',
                $tenantContext->organizationId()
            )
            ->where('status', 'active')
            ->orderBy('id')
            ->paginate(20);

        return OrganizationMemberResource::collection($members);
    }

    public function update(
        UpdateOrganizationMemberRequest $request,
        int $member,
        TenantContext $tenantContext
    ): JsonResponse {
        $target = $this->findTenantMember(
            $member,
            $tenantContext
        );

        $actor = $tenantContext->membership();
        $newRole = OrganizationRole::from(
            $request->validated('role')
        );

        if (
            $actor->role === OrganizationRole::Admin &&
            (
                $target->role === OrganizationRole::Owner ||
                $newRole === OrganizationRole::Owner
            )
        ) {
            return response()->json([
                'message' => 'Admin tidak dapat mengelola role owner.',
            ], 403);
        }

        if (
            $target->role === OrganizationRole::Owner &&
            $newRole !== OrganizationRole::Owner &&
            $this->ownerCount($tenantContext) <= 1
        ) {
            return response()->json([
                'message' => 'Organization harus memiliki minimal satu owner.',
            ], 422);
        }

        $target->update([
            'role' => $newRole,
        ]);

        return response()->json([
            'message' => 'Role anggota berhasil diperbarui.',
            'data' => new OrganizationMemberResource(
                $target->fresh('user')
            ),
        ]);
    }

    public function store(
        StoreOrganizationMemberRequest $request,
        TenantContext $tenantContext
    ): JsonResponse {
        $actor = $tenantContext->membership();
        if ($actor->role === OrganizationRole::Admin && $request->validated('role') === OrganizationRole::Owner->value) {
            return response()->json(['message' => 'Admin tidak dapat menambahkan owner.',], 403);
        }
        $user = User::query()->where('email', strtolower($request->validated('email')))->firstOrFail();
        $existing = OrganizationMember::query()->where('organization_id', $tenantContext->organizationId())->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'User sudah menjadi anggota organization.',], 422);
        }
        $membership = OrganizationMember::create(['organization_id' => $tenantContext->organizationId(), 'user_id' => $user->id, 'role' => $request->validated('role'), 'status' => 'active', 'joined_at' => now(), 'invited_by' => $request->user()->id,]);
        return response()->json(['message' => 'Anggota berhasil ditambahkan.', 'data' => new OrganizationMemberResource($membership->load('user')),], 201);
    }

    public function destroy(
        Request $request,
        int $member,
        TenantContext $tenantContext
    ): JsonResponse {
        $target = $this->findTenantMember(
            $member,
            $tenantContext
        );

        $actor = $tenantContext->membership();

        if (
            $actor->role === OrganizationRole::Admin &&
            $target->role === OrganizationRole::Owner
        ) {
            return response()->json([
                'message' => 'Admin tidak dapat menghapus owner.',
            ], 403);
        }

        if (
            $target->role === OrganizationRole::Owner &&
            $this->ownerCount($tenantContext) <= 1
        ) {
            return response()->json([
                'message' => 'Owner terakhir tidak dapat dihapus.',
            ], 422);
        }

        $target->delete();

        return response()->json([
            'message' => 'Anggota berhasil dihapus.',
        ]);
    }

    public function leave(
        Request $request,
        TenantContext $tenantContext
    ): JsonResponse {
        $membership = $tenantContext->membership();

        if (
            $membership->role === OrganizationRole::Owner &&
            $this->ownerCount($tenantContext) <= 1
        ) {
            return response()->json([
                'message' => 'Owner terakhir tidak dapat keluar dari organization.',
            ], 422);
        }

        $membership->delete();

        return response()->json([
            'message' => 'Anda berhasil keluar dari organization.',
        ]);
    }

    private function findTenantMember(
        int $memberId,
        TenantContext $tenantContext
    ): OrganizationMember {
        return OrganizationMember::query()
            ->with('user')
            ->where('organization_id', $tenantContext->organizationId())
            ->whereKey($memberId)
            ->where('status', 'active')
            ->firstOrFail();
    }

    private function ownerCount(
        TenantContext $tenantContext
    ): int {
        return OrganizationMember::query()
            ->where('organization_id', $tenantContext->organizationId())
            ->where('role', OrganizationRole::Owner->value)
            ->where('status', 'active')
            ->count();
    }
}
