<?php

namespace App\Http\Middleware;

use App\Models\OrganizationMember;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganization
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $organizationId = $request->header('X-Organization-ID');

        if (
            !$organizationId ||
            !ctype_digit((string) $organizationId)
        ) {
            return new JsonResponse([
                'message' => 'Header X-Organization-ID wajib diisi.',
            ], 422);
        }

        $user = $request->user();

        $membership = OrganizationMember::query()
            ->with('organization')
            ->where('organization_id', (int) $organizationId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (
            !$membership ||
            !$membership->organization ||
            $membership->organization->status !== 'active' ||
            $membership->organization->trashed()
        ) {
            return new JsonResponse([
                'message' => 'Organization tidak ditemukan.',
            ], 404);
        }

        $this->tenantContext->set(
            $membership->organization,
            $membership
        );

        $request->attributes->set(
            'organization',
            $membership->organization
        );

        $request->attributes->set(
            'organization_membership',
            $membership
        );

        return $next($request);
    }
}