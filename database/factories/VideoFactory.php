<?php

namespace Database\Factories;

use App\Enums\VideoStatus;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'youtube_id' => fake()->unique()->regexify('[A-Za-z0-9_-]{11}'),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'thumbnail_url' => fake()->imageUrl(),
            'duration_seconds' => fake()->numberBetween(120, 3600),
            'view_count' => fake()->numberBetween(0, 100000),
            'like_count' => fake()->numberBetween(0, 5000),
            'status' => VideoStatus::Published,
            'is_missing' => false,
            'youtube_published_at' => fake()->dateTimeBetween('-1 year'),
            'published_at' => fake()->dateTimeBetween('-1 year'),
            'synced_at' => now(),
        ];
    }
}
