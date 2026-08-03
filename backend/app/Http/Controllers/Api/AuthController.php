<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Support\Http\ApiResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);

        $token = $user->createToken('web-app')->plainTextToken;

        return ApiResponse::created(data: ['user' => $user, 'token' => $token,], message: 'Registrasi berhasil.');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $user->tokens()->where('name', 'web-app')->delete();

        $token = $user->createToken('web-app')->plainTextToken;

        $user->load([
            'organizations' => function ($query): void {
                $query
                    ->wherePivot('status', 'active')
                    ->where('organizations.status', 'active')
                    ->orderBy('organizations.name');
            },
        ]);

        return ApiResponse::success(data: ['user' => $user, 'token' => $token,], message: 'Login berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(data: $request->user(), message: 'Profil berhasil diambil.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return ApiResponse::success(message: 'Logout berhasil.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success(message: 'Semua sesi berhasil dikeluarkan.');
    }
}
