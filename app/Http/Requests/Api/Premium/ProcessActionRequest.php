<?php

namespace App\Http\Requests\Api\Premium;

use Illuminate\Foundation\Http\FormRequest;

class ProcessActionRequest extends FormRequest
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
            'action' => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action is required.',
        ];
    }
}
