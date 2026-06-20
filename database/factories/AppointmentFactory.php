<?php

namespace Database\Factories;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = CarbonImmutable::now()->addDays(fake()->numberBetween(1, 30))->setTime(fake()->numberBetween(9, 16), 0);

        return [
            'appointment_service_id' => AppointmentService::factory(),
            'reference' => 'RDV-'.Str::upper(Str::random(8)),
            'customer_first_name' => fake()->firstName(),
            'customer_last_name' => fake()->lastName(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'channel' => fake()->randomElement(AppointmentChannel::cases()),
            'notes' => fake()->optional()->sentence(),
            'starts_at' => $start,
            'ends_at' => $start->addMinutes(30),
            'status' => AppointmentStatus::Confirmed,
            'price_cents' => 0,
            'payment_status' => PaymentStatus::Unpaid,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => AppointmentStatus::Pending]);
    }
}
