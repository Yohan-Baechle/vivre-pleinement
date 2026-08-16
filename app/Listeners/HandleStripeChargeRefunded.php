<?php

namespace App\Listeners;

use App\Models\BookOrder;
use App\Models\Enrollment;
use App\Services\BookPaymentService;
use App\Services\CoursePaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;
use Throwable;

class HandleStripeChargeRefunded implements ShouldQueue
{
    /**
     * Un remboursement Stripe existe déjà : on retente plusieurs fois pour
     * ne pas laisser un accès actif alors que l'élève a été remboursé.
     */
    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [30, 60, 300, 900];

    public function __construct(
        private CoursePaymentService $coursePayments,
        private BookPaymentService $bookPayments,
    ) {}

    /**
     * Révoque l'accès lorsqu'un remboursement est émis depuis Stripe
     * (dashboard ou API) : l'inscription ou la commande liée au PaymentIntent
     * remboursé passe au statut Refunded.
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

        $order = BookOrder::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();

        if ($order !== null) {
            $this->bookPayments->refund($order);
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
