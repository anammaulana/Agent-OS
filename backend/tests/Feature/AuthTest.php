<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Anam Maulana',
            'email' => 'anam@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'Registrasi berhasil.'
            )
            ->assertJsonPath(
                'data.user.email',
                'anam@example.com'
            )
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Anam Maulana',
            'email' => 'anam@example.com',
        ]);

        $user = User::query()
            ->where('email', 'anam@example.com')
            ->firstOrFail();

        $this->assertTrue(
            Hash::check('password123', $user->password)
        );
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'email-tidak-valid',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'anam@example.com',
        ]);

        $this->postJson('/api/auth/register', [
            'name' => 'Anam',
            'email' => 'anam@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'anam@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'anam@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Login berhasil.'
            )
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'anam@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'anam@example.com',
            'password' => 'password-salah',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->createToken('test')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $user->id
            )
            ->assertJsonPath(
                'data.email',
                $user->email
            );
    }

    public function test_guest_cannot_get_profile(): void
    {
        $this
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->createToken('test')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Logout berhasil.'
            );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }
}