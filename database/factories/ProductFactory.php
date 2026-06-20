<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'price_cents' => fake()->numberBetween(1000, 20000),
            'currency' => 'EUR',
            'is_active' => true,
        ];
    }
}
