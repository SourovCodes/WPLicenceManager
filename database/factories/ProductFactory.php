<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['plugin', 'theme']),
            'version' => fake()->semver(),
            'download_url' => fake()->optional()->url(),
            'is_active' => true,
            'requires_license' => true,
            'has_api_access' => fake()->boolean(30),
        ];
    }

    public function plugin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'plugin',
        ]);
    }

    public function theme(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'theme',
        ]);
    }

    public function withApiAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_api_access' => true,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_license' => false,
        ]);
    }
}
