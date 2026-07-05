<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'status' => EnrollmentStatus::Active,
            'amount_paid_cents' => fake()->numberBetween(4900, 29900),
            'currency' => 'EUR',
            'stripe_payment_intent_id' => 'pi_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'purchased_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentStatus::Pending,
            'amount_paid_cents' => 0,
            'stripe_payment_intent_id' => null,
            'purchased_at' => null,
        ]);
    }
}
