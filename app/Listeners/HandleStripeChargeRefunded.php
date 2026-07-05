<?php

namespace App\Listeners;

use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;

class HandleStripeChargeRefunded implements ShouldQueue
{
    public function __construct(
        private CoursePaymentService $coursePayments,
    ) {}

    /**
     * Révoque l'accès à une formation lorsqu'un remboursement est émis depuis
     * Stripe (dashboard ou API) : l'inscription liée au PaymentIntent remboursé
     * passe au statut Refunded.
     */
    public function handle(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'charge.refunded') {
            return;
        }

        $charge = $event->payload['data']['object'] ?? [];
        $paymentIntentId = is_string($charge['payment_intent'] ?? null) ? $charge['payment_intent'] : null;

        if ($paymentIntentId === null) {
            return;
        }

        $enrollment = Enrollment::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();

        if ($enrollment !== null) {
            $this->coursePayments->refund($enrollment);
        }
    }
}
