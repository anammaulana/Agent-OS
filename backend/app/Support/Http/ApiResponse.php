<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Berhasil.', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message, 'data' => $data,];
        if ($meta !== []) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }
    public static function created(mixed $data = null, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return self::success(data: $data, message: $message, status: 201);
    }
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
    public static function error(string $message, int $status, ?string $code = null, array $errors = [], array $meta = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message,];
        if ($code !== null) {
            $payload['code'] = $code;
        }
        if ($errors !== []) {
            $payload['errors'] = $errors;
        }
        if ($meta !== []) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }
}
