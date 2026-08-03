<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Services\Organization\OrganizationService;
use App\Support\Http\ApiResponse;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly OrganizationService $service
    ) {
    }

    public function index(Request $request)
    {
        return OrganizationResource::collection(
            $this->service->listForUser($request->user())
        );
    }

    public function store(
        StoreOrganizationRequest $request
    ): JsonResponse {
        $organization = $this->service->create(
            actor: $request->user(),
            data: $request->validated()
        );

        return ApiResponse::created(
            data: new OrganizationResource($organization),
            message: 'Organization berhasil dibuat.'
        );
    }

    public function show(
        TenantContext $tenantContext
    ): OrganizationResource {
        return new OrganizationResource(
            $tenantContext->organization()
        );
    }

    public function update(
        UpdateOrganizationRequest $request
    ): JsonResponse {
        $organization = $this->service->update(
            $request->validated()
        );

        return ApiResponse::success(
            data: new OrganizationResource($organization),
            message: 'Organization berhasil diperbarui.'
        );
    }

    public function destroy(): JsonResponse
    {
        $this->service->deactivate();

        return ApiResponse::success(
            message: 'Organization berhasil dinonaktifkan.'
        );
    }
}
