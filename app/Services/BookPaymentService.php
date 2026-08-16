<?php

namespace App\Services;

use App\Enums\BookOrderStatus;
use App\Mail\BookOrderConfirmation;
use App\Mail\BookOrderNotification;
use App\Models\BookOrder;
use App\Support\SiteContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;

class BookPaymentService
{
    public function __construct(
        private StripePaymentIntents $intents,
    ) {}

    /**
     * Retourne le PaymentIntent de la commande : réutilise celui déjà créé
     * (rafraîchissement, second onglet) tant qu'il n'est pas finalisé, sinon
     * en crée un nouveau. La commande est marquée payée plus tard par le
     * webhook payment_intent.succeeded.
     */
    public function getOrCreatePaymentIntent(BookOrder $order): PaymentIntent
    {
        $order->loadMissing('product');

        $existing = $this->intents->reusable(
            $order->stripe_payment_intent_id,
            $order->amount_cents,
        );

        if ($existing !== null) {
            return $existing;
        }

        $intent = $this->intents->create([
            'amount' => $order->amount_cents,
            'currency' => strtolower($order->currency),
            'description' => $order->product->name,
            'receipt_email' => $order->customer_email,
            'metadata' => [
                'book_order_id' => $order->id,
                'product_id' => $order->product_id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $order->update(['stripe_payment_intent_id' => $intent->id]);

        return $intent;
    }

    /**
     * Marque la commande payée puis envoie le lien de téléchargement au client
     * et la notification à l'admin. Idempotent : un webhook dupliqué pour une
     * commande déjà payée ne fait rien, un paiement concurrent (intent
     * différent) est remboursé d'office.
     *
     * Seules la vérification et la mise à jour du statut sont verrouillées :
     * l'appel réseau Stripe et l'envoi des mails se font après le COMMIT, pour
     * ne jamais faire lire à un job de mail en file un enregistrement pas
     * encore validé. Le montant enregistré vient du payload Stripe : c'est ce
     * qui a réellement été payé, pas le prix courant du produit.
     */
    public function fulfill(
        BookOrder $order,
        ?string $paymentIntentId = null,
        ?int $amountReceivedCents = null,
        ?string $currency = null,
    ): void {
        [$outcome, $locked] = DB::transaction(function () use ($order, $paymentIntentId, $amountReceivedCents, $currency) {
            /** @var BookOrder $locked */
            $locked = BookOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === BookOrderStatus::Paid) {
                return ['duplicate', $locked];
            }

            $locked->update([
                'status' => BookOrderStatus::Paid,
                'amount_cents' => $amountReceivedCents ?? $locked->amount_cents,
                'currency' => $currency !== null ? strtoupper($currency) : $locked->currency,
                'stripe_payment_intent_id' => $paymentIntentId ?? $locked->stripe_payment_intent_id,
                'paid_at' => now(),
            ]);

            return ['fulfilled', $locked];
        });

        match ($outcome) {
            'duplicate' => $this->refundDuplicatePayment($locked, $paymentIntentId),
            'fulfilled' => $this->sendConfirmation($locked),
        };
    }

    private function sendConfirmation(BookOrder $order): void
    {
        $fresh = $order->fresh('product');

        Mail::to($order->customer_email)->send(new BookOrderConfirmation($fresh));
        Mail::to(SiteContact::notifyEmail())->send(new BookOrderNotification($fresh));
    }

    /**
     * Un paiement réussi arrive pour une commande déjà payée via un intent
     * différent : le client a payé deux fois (deux onglets avant la
     * réutilisation d'intent). On rembourse le second débit.
     */
    private function refundDuplicatePayment(BookOrder $order, ?string $paymentIntentId): void
    {
        if ($paymentIntentId === null || $paymentIntentId === $order->stripe_payment_intent_id) {
            return;
        }

        if ($this->intents->refundQuietly($paymentIntentId)) {
            Log::warning('Second paiement détecté pour une commande livre déjà payée : remboursé automatiquement.', [
                'book_order_id' => $order->id,
                'kept_payment_intent_id' => $order->stripe_payment_intent_id,
                'refunded_payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        Log::error('Second paiement détecté pour une commande livre déjà payée mais remboursement impossible.', [
            'book_order_id' => $order->id,
            'payment_intent_id' => $paymentIntentId,
        ]);
    }

    /**
     * Coupe l'accès au téléchargement après un remboursement (webhook
     * charge.refunded ou action admin). Idempotent : une commande déjà
     * remboursée ou jamais payée ne bouge pas.
     */
    public function refund(BookOrder $order): void
    {
        if ($order->status !== BookOrderStatus::Paid) {
            return;
        }

        $order->update([
            'status' => BookOrderStatus::Refunded,
            'refunded_at' => now(),
        ]);

        Log::info('Commande livre remboursée, téléchargement révoqué.', [
            'book_order_id' => $order->id,
            'product_id' => $order->product_id,
        ]);
    }
}
