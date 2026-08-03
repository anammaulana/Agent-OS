<?php

use App\Exceptions\DomainException;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\RequireOrganizationRole;
use App\Http\Middleware\ResolveOrganization;
use App\Support\Http\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Event\Code\Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))->withRouting(web: __DIR__ . '/../routes/web.php', api: __DIR__ . '/../routes/api.php', commands: __DIR__ . '/../routes/console.php', health: '/up',)->withMiddleware(function (Middleware $middleware): void {
    $middleware->prepend(AssignRequestId::class);
    $middleware->alias(['organization' => ResolveOrganization::class, 'organization.role' => RequireOrganizationRole::class,]);
})->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(fn(Request $request, Throwable $exception): bool => $request->is('api/*') || $request->expectsJson());
    $exceptions->render(function (DomainException $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        return ApiResponse::error(message: $exception->getMessage(), status: $exception->status(), code: $exception->errorCode(), errors: $exception->errors());
    });
    $exceptions->render(function (ValidationException $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        return ApiResponse::error(message: 'Data yang diberikan tidak valid.', status: 422, code: 'VALIDATION_ERROR', errors: $exception->errors());
    });
    $exceptions->render(function (AuthenticationException $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        return ApiResponse::error(message: 'Anda belum terautentikasi.', status: 401, code: 'UNAUTHENTICATED');
    });
    $exceptions->render(function (AuthorizationException $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        return ApiResponse::error(message: 'Anda tidak memiliki izin.', status: 403, code: 'FORBIDDEN');
    });
    $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        return ApiResponse::error(message: 'Resource tidak ditemukan.', status: 404, code: 'RESOURCE_NOT_FOUND');
    });
    $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
        if (!$request->is('api/*')) {
            return null;
        }
        $status = $exception->getStatusCode();
        return ApiResponse::error(message: match ($status) {
            403 => 'Anda tidak memiliki izin.',
            404 => 'Endpoint tidak ditemukan.',
            405 => 'Metode HTTP tidak diizinkan.',
            429 => 'Terlalu banyak request.',
            default => $status >= 500 ? 'Terjadi kesalahan pada server.' : ($exception->getMessage() ?: 'Request gagal.'),
        }, status: $status, code: match ($status) {
            403 => 'FORBIDDEN',
            404 => 'ENDPOINT_NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            429 => 'TOO_MANY_REQUESTS',
            default => 'HTTP_ERROR',
        });
    });
    $exceptions->render(function (Throwable $exception, Request $request) {
        if (!$request->is('api/*') || config('app.debug')) {
            return null;
        }
        return ApiResponse::error(message: 'Terjadi kesalahan pada server.', status: 500, code: 'INTERNAL_SERVER_ERROR');
    });
})->create();
