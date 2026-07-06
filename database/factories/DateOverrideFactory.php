<?php

namespace Database\Factories;

use App\Models\DateOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DateOverride>
 */
class DateOverrideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Defaults to a full-day closure (no start/end time), the most common
     * case exercised by the appointment slot tests.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('+1 day', '+60 days')->format('Y-m-d'),
            'start_time' => null,
            'end_time' => null,
            'reason' => fake()->optional()->sentence(3),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'start_time' => null,
            'end_time' => null,
        ]);
    }

    public function partial(string $startTime = '09:00', string $endTime = '10:00'): static
    {
        return $this->state(fn () => [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }
}
