<?php

namespace Database\Factories;

use App\Models\AppointmentService;
use App\Models\Availability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_service_id' => null,
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '08:00',
            'end_time' => '20:00',
            'is_active' => true,
        ];
    }

    public function dayOfWeek(int $dayOfWeek): static
    {
        return $this->state(fn () => ['day_of_week' => $dayOfWeek]);
    }

    public function forService(AppointmentService $service): static
    {
        return $this->state(fn () => ['appointment_service_id' => $service->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
