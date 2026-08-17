<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'module_id' => Module::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'content' => fake()->paragraphs(3, true),
            'video_provider' => 'youtube',
            'video_id' => fake()->regexify('[A-Za-z0-9_-]{11}'),
            'duration_seconds' => fake()->numberBetween(120, 1800),
            'position' => fake()->numberBetween(0, 10),
            'is_free_preview' => false,
        ];
    }

    public function freePreview(): static
    {
        return $this->state(fn () => ['is_free_preview' => true]);
    }
}
