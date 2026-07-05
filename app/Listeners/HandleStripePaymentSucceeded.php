<?php

namespace App\Listeners;

use App\Models\Appointment;
use App\Models\Enrollment;
use App\Services\BookingPaymentService;
use App\Services\CoursePaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;

class HandleStripePaymentSucceeded implements ShouldQueue
{
    public function __construct(
        private BookingPaymentService $bookingPayments,
        private CoursePaymentService $coursePayments,
    ) {}

    /**
     * Route le webhook payment_intent.succeeded vers le bon domaine selon les
     * métadonnées : un rendez-vous (appointment_id) ou une formation (enrollment_id).
     */
    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'payment_intent.succeeded') {
            return;
        }

        $intent = $event->payload['data']['object'] ?? [];
        $metadata = $intent['metadata'] ?? [];
        $paymentIntentId = is_string($intent['id'] ?? null) ? $intent['id'] : null;

        if (isset($metadata['appointment_id'])) {
            $this->fulfillAppointment($metadata['appointment_id'], $paymentIntentId);

            return;
        }

        if (isset($metadata['enrollment_id'])) {
            $this->fulfillEnrollment($metadata['enrollment_id'], $paymentIntentId, $intent);
        }
    }

    private function fulfillAppointment(mixed $appointmentId, ?string $paymentIntentId): void
    {
        $appointment = Appointment::query()->find($appointmentId);

        if ($appointment !== null) {
            $this->bookingPayments->fulfill($appointment, $paymentIntentId);
        }
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function fulfillEnrollment(mixed $enrollmentId, ?string $paymentIntentId, array $intent): void
    {
        $enrollment = Enrollment::query()->find($enrollmentId);

        if ($enrollment !== null) {
            $this->coursePayments->fulfill(
                $enrollment,
                $paymentIntentId,
                is_int($intent['amount_received'] ?? null) ? $intent['amount_received'] : null,
                is_string($intent['currency'] ?? null) ? $intent['currency'] : null,
            );
        }
    }
}
