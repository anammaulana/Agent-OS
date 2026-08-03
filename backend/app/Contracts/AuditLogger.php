<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuditLogger
{
    public function record(string $action, ?Model $resource = null, ?string $description = null, array $oldValues = [], array $newValues = [], array $metadata = [], ?int $organizationId = null, ?int $actorId = null): void;
}
