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
            $this->fulfillEnrollment($metadata['enrollment_id'], $paymentIntentId);
        }
    }

    private function fulfillAppointment(mixed $appointmentId, ?string $paymentIntentId): void
    {
        $appointment = Appointment::query()->find($appointmentId);

        if ($appointment !== null) {
            $this->bookingPayments->fulfill($appointment, $paymentIntentId);
        }
    }

    private function fulfillEnrollment(mixed $enrollmentId, ?string $paymentIntentId): void
    {
        $enrollment = Enrollment::query()->find($enrollmentId);

        if ($enrollment !== null) {
            $this->coursePayments->fulfill($enrollment, $paymentIntentId);
        }
    }
}
