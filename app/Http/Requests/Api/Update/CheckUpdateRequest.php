<?php

namespace App\Http\Requests\Api\Update;

use Illuminate\Foundation\Http\FormRequest;

class CheckUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'product_slug' => ['required', 'string', 'max:255'],
            'current_version' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'domain.required' => 'Domain is required.',
            'product_slug.required' => 'Product slug is required.',
            'current_version.required' => 'Current version is required.',
        ];
    }
}
