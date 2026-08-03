<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;
    protected $fillable = ['organization_id', 'actor_id', 'action', 'resource_type', 'resource_id', 'description', 'old_values', 'new_values', 'metadata', 'request_id', 'ip_address', 'user_agent', 'created_at',];
    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'metadata' => 'array', 'created_at' => 'datetime',];
    }
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
