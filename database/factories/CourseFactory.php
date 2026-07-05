<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(4, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'outcomes' => [fake()->sentence(), fake()->sentence(), fake()->sentence()],
            'price_cents' => fake()->numberBetween(4900, 29900),
            'currency' => 'EUR',
            'status' => CourseStatus::Published,
            'published_at' => now()->subDay(),
            'position' => fake()->numberBetween(0, 20),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ]);
    }
}
