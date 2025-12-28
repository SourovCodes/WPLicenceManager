<?php

namespace App\Http\Requests\Api\Update;

use Illuminate\Foundation\Http\FormRequest;

class DownloadUpdateRequest extends FormRequest
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
        ];
    }
}
