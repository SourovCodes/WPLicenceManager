<?php

namespace App\Http\Requests\Api\Premium;

use Illuminate\Foundation\Http\FormRequest;

class CheckSftpCredentialsRequest extends FormRequest
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
            'sftp_host' => ['required', 'string', 'max:255'],
            'sftp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'sftp_username' => ['required', 'string', 'max:255'],
            'sftp_password' => ['required', 'string', 'max:255'],
            'sftp_remote_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sftp_host.required' => 'SFTP host is required.',
            'sftp_username.required' => 'SFTP username is required.',
            'sftp_password.required' => 'SFTP password is required.',
        ];
    }
}
