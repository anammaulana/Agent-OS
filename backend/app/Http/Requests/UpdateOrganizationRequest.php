<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organization = $this->attributes->get('organization');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('organizations', 'slug')
                    ->ignore($organization?->id),
            ],
            'timezone' => [
                'sometimes',
                'required',
                'timezone',
            ],
            'locale' => [
                'sometimes',
                'required',
                'string',
                'max:10',
            ],
        ];
    }
}