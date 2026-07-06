<?php

namespace App\Listeners;

use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;
use Throwable;

class HandleStripeChargeRefunded implements ShouldQueue
{
    /**
     * @var int
     */
    public $tries = 5;

    /**
     * @var array<int, int>
     */
    public $backoff = [30, 60, 300, 900];

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

    /**
     * Toutes les tentatives ont échoué : un remboursement Stripe existe sans
     * que l'accès à la formation ait été révoqué côté application.
     */
    public function failed(WebhookReceived $event, Throwable $exception): void
    {
        report($exception);

        Log::critical('Échec définitif du traitement de charge.refunded : accès formation potentiellement non révoqué.', [
            'payload' => $event->payload,
            'exception' => $exception->getMessage(),
        ]);
    }
}
