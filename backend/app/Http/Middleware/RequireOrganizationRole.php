<?php

namespace App\Http\Middleware;

use App\Support\Http\ApiResponse;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOrganizationRole
{
    public function __construct(private readonly TenantContext $tenantContext) {}
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $currentRole = $this->tenantContext->membership()->role->value;
        if (!in_array($currentRole, $allowedRoles, true)) {
            return ApiResponse::error(message: 'Anda tidak memiliki izin.', status: 403, code: 'INSUFFICIENT_ORGANIZATION_ROLE');
        }
        return $next($request);
    }
}
