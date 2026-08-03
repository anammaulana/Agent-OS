<?php

namespace App\Http\Middleware;

use App\Models\OrganizationMember;
use App\Support\Http\ApiResponse;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganization
{
    public function __construct(private readonly TenantContext $tenantContext) {}
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->header('X-Organization-ID');
        if (!$organizationId || !ctype_digit((string) $organizationId)) {
            return ApiResponse::error(message: 'Header X-Organization-ID wajib diisi.', status: 422, code: 'ORGANIZATION_HEADER_REQUIRED');
        }
        $user = $request->user();
        $membership = OrganizationMember::query()->with('organization')->where('organization_id', (int) $organizationId)->where('user_id', $user->id)->where('status', 'active')->first();
        if (!$membership || !$membership->organization || $membership->organization->status !== 'active' || $membership->organization->trashed()) { /* * Gunakan 404 untuk tidak membocorkan apakah tenant * tersebut benar-benar ada. */
            return ApiResponse::error(message: 'Organization tidak ditemukan.', status: 404, code: 'ORGANIZATION_NOT_FOUND');
        }
        $this->tenantContext->set(organization: $membership->organization, membership: $membership);
        $request->attributes->set('organization', $membership->organization);
        $request->attributes->set('organization_membership', $membership);
        return $next($request);
    }
}
