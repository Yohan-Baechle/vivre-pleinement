<?php

namespace Database\Factories;

use App\Models\AppointmentService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AppointmentService>
 */
class AppointmentServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->sentence(),
            'duration_minutes' => fake()->randomElement([30, 45, 60]),
            'price_cents' => fake()->randomElement([0, 5000, 8000]),
            'currency' => 'EUR',
            'buffer_minutes' => 0,
            'min_notice_hours' => 12,
            'max_advance_days' => 60,
            'requires_confirmation' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function requiresConfirmation(): static
    {
        return $this->state(fn () => ['requires_confirmation' => true]);
    }
}
