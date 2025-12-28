<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LicenseActivation>
 */
class LicenseActivationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'domain' => fake()->domainName(),
            'ip_address' => fake()->ipv4(),
            'local_key' => Str::random(64),
            'is_active' => true,
            'activated_at' => now(),
        ];
    }

    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivation_reason' => 'Manual deactivation',
        ]);
    }
}
