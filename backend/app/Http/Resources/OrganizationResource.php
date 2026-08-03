<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = null;

        if ($this->relationLoaded('memberships')) {
            $role = $this->memberships->first()?->role?->value;
        }

        if ($this->pivot) {
            $role = is_object($this->pivot->role)
                ? $this->pivot->role->value
                : $this->pivot->role;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_path' => $this->logo_path,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'status' => $this->status,
            'current_user_role' => $role,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}