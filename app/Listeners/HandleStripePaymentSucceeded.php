<?php

namespace App\Listeners;

use App\Models\Appointment;
use App\Services\BookingPaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;

class HandleStripePaymentSucceeded implements ShouldQueue
{
    public function __construct(
        private BookingPaymentService $payments,
    ) {}

    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'payment_intent.succeeded') {
            return;
        }

        $intent = $event->payload['data']['object'] ?? [];
        $appointmentId = $intent['metadata']['appointment_id'] ?? null;

        if ($appointmentId === null) {
            return;
        }

        $appointment = Appointment::query()->find($appointmentId);

        if ($appointment === null) {
            return;
        }

        $paymentIntentId = $intent['id'] ?? null;

        $this->payments->fulfill($appointment, is_string($paymentIntentId) ? $paymentIntentId : null);
    }
}
