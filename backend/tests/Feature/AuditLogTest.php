<?php

namespace Tests\Feature;

use App\Contracts\AuditLogger;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;
    public function test_creating_organization_writes_audit_log(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/organizations', ['name' => 'Audit Organization', 'timezone' => 'Asia/Jakarta', 'locale' => 'id',]);
        $response->assertCreated();
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'action' => 'organization.created', 'description' => 'Organization dibuat.',]);
    }
    public function test_audit_logger_redacts_sensitive_values(): void
    {
        $this->app->make(AuditLogger::class)->record(action: 'security.test', newValues: ['name' => 'Safe Name', 'api_key' => 'secret-api-key', 'nested' => ['client_secret' => 'secret-value',],]);
        $audit = AuditLog::query()->firstOrFail();
        $this->assertSame('Safe Name', $audit->new_values['name']);
        $this->assertSame('[REDACTED]', $audit->new_values['api_key']);
        $this->assertSame('[REDACTED]', $audit->new_values['nested']['client_secret']);
    }
}
