<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = $request->user()
            ->organizations()
            ->wherePivot('status', 'active')
            ->where('organizations.status', 'active')
            ->orderBy('organizations.name')
            ->get();

        return OrganizationResource::collection($organizations);
    }

    public function store(
        StoreOrganizationRequest $request
    ): JsonResponse {
        $organization = DB::transaction(
            function () use ($request): Organization {
                $validated = $request->validated();

                $organization = Organization::create([
                    'name' => $validated['name'],
                    'slug' => $this->generateUniqueSlug(
                        $validated['name']
                    ),
                    'timezone' => $validated['timezone']
                        ?? 'Asia/Jakarta',
                    'locale' => $validated['locale'] ?? 'id',
                    'status' => 'active',
                    'created_by' => $request->user()->id,
                ]);

                $organization->memberships()->create([
                    'user_id' => $request->user()->id,
                    'role' => OrganizationRole::Owner,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);

                return $organization;
            }
        );

        return response()->json([
            'message' => 'Organization berhasil dibuat.',
            'data' => new OrganizationResource($organization),
        ], 201);
    }

    public function show(
        TenantContext $tenantContext
    ): OrganizationResource {
        return new OrganizationResource(
            $tenantContext->organization()
        );
    }

    public function update(
        UpdateOrganizationRequest $request,
        TenantContext $tenantContext
    ): JsonResponse {
        $organization = $tenantContext->organization();

        $organization->update($request->validated());

        return response()->json([
            'message' => 'Organization berhasil diperbarui.',
            'data' => new OrganizationResource(
                $organization->fresh()
            ),
        ]);
    }

    public function destroy(
        TenantContext $tenantContext
    ): JsonResponse {
        $organization = $tenantContext->organization();

        DB::transaction(function () use ($organization): void {
            $organization->update([
                'status' => 'inactive',
            ]);

            $organization->delete();
        });

        return response()->json([
            'message' => 'Organization berhasil dinonaktifkan.',
        ]);
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'organization';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Organization::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}