<?php

namespace App\Http\Requests\Api\License;

use Illuminate\Foundation\Http\FormRequest;

class LicenseStatusRequest extends FormRequest
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
            'product_slug' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'product_slug.required' => 'Product slug is required.',
        ];
    }
}
