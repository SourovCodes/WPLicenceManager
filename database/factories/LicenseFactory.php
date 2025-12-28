<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'license_key' => License::generateLicenseKey(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'status' => 'inactive',
            'validity_days' => 365,
            'max_domain_changes' => 3,
            'domain_changes_used' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'activated_at' => now(),
            'expires_at' => now()->addDays($attributes['validity_days'] ?? 365),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'activated_at' => now()->subYear(),
            'expires_at' => now()->subDays(30),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
        ]);
    }
}
