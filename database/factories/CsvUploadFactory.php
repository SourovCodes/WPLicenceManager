<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CsvUpload>
 */
class CsvUploadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'original_filename' => fake()->word().'.csv',
            'stored_path' => 'csv-uploads/'.fake()->uuid().'.csv',
            'sftp_host' => fake()->domainName(),
            'sftp_port' => 22,
            'sftp_username' => fake()->userName(),
            'sftp_password' => fake()->password(),
            'sftp_remote_path' => '/uploads',
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'SFTP connection failed.',
            'processed_at' => now(),
        ]);
    }
}
