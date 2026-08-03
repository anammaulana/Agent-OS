<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOrganizationRole
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string ...$allowedRoles
    ): Response {
        $currentRole = $this->tenantContext
            ->membership()
            ->role
            ->value;

        if (!in_array($currentRole, $allowedRoles, true)) {
            return new JsonResponse([
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        return $next($request);
    }
}