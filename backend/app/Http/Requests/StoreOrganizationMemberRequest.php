<?php

namespace App\Http\Requests;

use App\Enums\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
            'role' => [
                'required',
                Rule::enum(OrganizationRole::class),
                Rule::notIn([
                    OrganizationRole::Owner->value,
                ]),
            ],
        ];
    }
}