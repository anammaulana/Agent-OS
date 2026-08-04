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
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AssignRequestId::class);

        /*
     * Backend ini merupakan REST API.
     *
     * Untuk request API, jangan mencoba redirect ke named
     * route "login" karena route login berada di Vue frontend.
     */
        $middleware->redirectGuestsTo(
            fn(Request $request): ?string =>
            $request->is('api/*')
                ? null
                : '/login'
        );

        $middleware->alias([
            'organization' => ResolveOrganization::class,
            'organization.role' => RequireOrganizationRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Semua endpoint API harus menghasilkan JSON.
         */
        $exceptions->shouldRenderJsonWhen(
            function (
                Request $request,
                \Throwable $exception
            ): bool {
                return $request->is('api/*')
                    || $request->expectsJson();
            }
        );

        /*
         * Business/domain error.
         */
        $exceptions->render(
            function (
                DomainException $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    message: $exception->getMessage(),
                    status: $exception->status(),
                    code: $exception->errorCode(),
                    errors: $exception->errors()
                );
            }
        );

        /*
         * Validation error.
         */
        $exceptions->render(
            function (
                ValidationException $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    message: 'Data yang diberikan tidak valid.',
                    status: 422,
                    code: 'VALIDATION_ERROR',
                    errors: $exception->errors()
                );
            }
        );

        /*
         * User belum login atau token tidak valid.
         */
        $exceptions->render(
            function (
                AuthenticationException $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    message: 'Anda belum terautentikasi.',
                    status: 401,
                    code: 'UNAUTHENTICATED'
                );
            }
        );

        /*
         * User login tetapi tidak memiliki izin.
         */
        $exceptions->render(
            function (
                AuthorizationException $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    message: 'Anda tidak memiliki izin.',
                    status: 403,
                    code: 'FORBIDDEN'
                );
            }
        );

        /*
         * Model/resource database tidak ditemukan.
         */
        $exceptions->render(
            function (
                ModelNotFoundException $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    message: 'Resource tidak ditemukan.',
                    status: 404,
                    code: 'RESOURCE_NOT_FOUND'
                );
            }
        );

        /*
         * HTTP exception seperti 404, 405, dan 429.
         */
        $exceptions->render(
            function (
                HttpExceptionInterface $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                $status = $exception->getStatusCode();

                $message = match ($status) {
                    400 => 'Request tidak valid.',
                    401 => 'Anda belum terautentikasi.',
                    403 => 'Anda tidak memiliki izin.',
                    404 => 'Endpoint tidak ditemukan.',
                    405 => 'Metode HTTP tidak diizinkan.',
                    409 => 'Terjadi konflik data.',
                    419 => 'Sesi telah kedaluwarsa.',
                    422 => 'Data yang diberikan tidak valid.',
                    429 => 'Terlalu banyak request.',
                    default => $status >= 500
                        ? 'Terjadi kesalahan pada server.'
                        : (
                            $exception->getMessage()
                            ?: 'Request gagal.'
                        ),
                };

                $code = match ($status) {
                    400 => 'BAD_REQUEST',
                    401 => 'UNAUTHENTICATED',
                    403 => 'FORBIDDEN',
                    404 => 'ENDPOINT_NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    409 => 'CONFLICT',
                    419 => 'SESSION_EXPIRED',
                    422 => 'UNPROCESSABLE_ENTITY',
                    429 => 'TOO_MANY_REQUESTS',
                    default => $status >= 500
                        ? 'SERVER_ERROR'
                        : 'HTTP_ERROR',
                };

                return ApiResponse::error(
                    message: $message,
                    status: $status,
                    code: $code
                );
            }
        );

        /*
         * Error umum production.
         *
         * Saat APP_DEBUG=true, Laravel tetap menampilkan detail
         * error agar proses development lebih mudah.
         */
        $exceptions->render(
            function (
                \Throwable $exception,
                Request $request
            ) {
                if (!$request->is('api/*')) {
                    return null;
                }

                if (config('app.debug')) {
                    return null;
                }

                return ApiResponse::error(
                    message: 'Terjadi kesalahan pada server.',
                    status: 500,
                    code: 'INTERNAL_SERVER_ERROR'
                );
            }
        );
    })
    ->create();