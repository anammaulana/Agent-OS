<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

    /*
     * Route yang tidak membutuhkan organization aktif.
     */
    Route::get(
        '/organizations',
        [OrganizationController::class, 'index']
    );

    Route::post(
        '/organizations',
        [OrganizationController::class, 'store']
    );

    /*
     * Route yang membutuhkan header X-Organization-ID.
     */
    Route::middleware('organization')->group(function (): void {
        Route::get(
            '/organization',
            [OrganizationController::class, 'show']
        );

        Route::get(
            '/organization/members',
            [OrganizationMemberController::class, 'index']
        );

        Route::post(
            '/organization/leave',
            [OrganizationMemberController::class, 'leave']
        );

        Route::middleware(
            'organization.role:owner,admin'
        )->group(function (): void {
            Route::put(
                '/organization',
                [OrganizationController::class, 'update']
            );

            Route::post(
                '/organization/members',
                [OrganizationMemberController::class, 'store']
            );

            Route::patch(
                '/organization/members/{member}',
                [OrganizationMemberController::class, 'update']
            );

            Route::delete(
                '/organization/members/{member}',
                [OrganizationMemberController::class, 'destroy']
            );
        });

        Route::delete(
            '/organization',
            [OrganizationController::class, 'destroy']
        )->middleware('organization.role:owner');
    });
});