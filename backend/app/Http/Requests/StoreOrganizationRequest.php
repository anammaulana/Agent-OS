<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'timezone' => [
                'nullable',
                'timezone',
            ],
            'locale' => [
                'nullable',
                'string',
                'max:10',
            ],
        ];
    }
}