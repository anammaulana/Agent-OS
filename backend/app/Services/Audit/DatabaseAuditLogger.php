<?php

namespace App\Services\Audit;

use App\Contracts\AuditLogger;
use App\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class DatabaseAuditLogger implements AuditLogger
{
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'token', 'access_token', 'refresh_token', 'api_key', 'secret', 'client_secret', 'authorization', 'credential', 'credentials',];
    public function __construct(private readonly Request $request, private readonly TenantContext $tenantContext) {}
    public function record(string $action, ?Model $resource = null, ?string $description = null, array $oldValues = [], array $newValues = [], array $metadata = [], ?int $organizationId = null, ?int $actorId = null): void
    {
        try {
            $organizationId ??= $this->tenantContext->hasOrganization() ? $this->tenantContext->organizationId() : null;
            $actorId ??= $this->request->user()?->id;
            AuditLog::query()->create(['organization_id' => $organizationId, 'actor_id' => $actorId, 'action' => $action, 'resource_type' => $resource ? $resource::class : null, 'resource_id' => $resource ? (string) $resource->getKey() : null, 'description' => $description, 'old_values' => $this->sanitize($oldValues), 'new_values' => $this->sanitize($newValues), 'metadata' => $this->sanitize($metadata), 'request_id' => $this->request->attributes->get('request_id'), 'ip_address' => $this->request->ip(), 'user_agent' => mb_substr((string) $this->request->userAgent(), 0, 1000), 'created_at' => now(),]);
        } catch (Throwable $exception) { /* * Audit log tidak boleh menggagalkan transaksi bisnis * non-kritis. Error tetap masuk application log. */
            report($exception);
        }
    }
    private function sanitize(array $values): array
    {
        return collect($values)->mapWithKeys(function (mixed $value, string|int $key): array {
            $normalizedKey = strtolower((string) $key);
            if ($this->isSensitive($normalizedKey)) {
                return [$key => '[REDACTED]'];
            }
            if (is_array($value)) {
                return [$key => $this->sanitize($value)];
            }
            return [$key => $value];
        })->all();
    }
    private function isSensitive(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }
        return false;
    }
}
