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

    public function short(): static
    {
        return $this->state(fn () => ['duration_seconds' => 45]);
    }

    /**
     * Sous-titres tout juste récupérés : un seul bloc sans ponctuation.
     */
    public function withRawTranscript(int $words = 300): static
    {
        return $this->state(fn () => [
            'transcript' => '<p>'.implode(' ', fake()->words($words)).'</p>',
            'transcript_formatted_at' => null,
        ]);
    }

    /**
     * Transcription reponctuée en paragraphes par l'étape IA.
     */
    public function withFormattedTranscript(): static
    {
        return $this->state(fn () => [
            'transcript' => '<p>'.fake()->paragraph().'</p>'
                .'<p>'.fake()->paragraph().'</p>',
            'transcript_formatted_at' => now(),
        ]);
    }
}
