<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;
use Throwable;

/**
 * Cycle de vie d'un PaymentIntent Stripe, partagé par les deux tunnels d'achat
 * (rendez-vous et formations). Ne connaît aucun modèle du domaine : les
 * services appelants gardent la responsabilité de ce qu'un paiement signifie
 * chez eux.
 */
class StripePaymentIntents
{
    /**
     * Statuts pour lesquels un PaymentIntent existant peut resservir au Payment
     * Element au lieu d'en créer un nouveau (risque de double débit sinon).
     *
     * @var list<string>
     */
    private const REUSABLE_STATUSES = [
        'requires_payment_method',
        'requires_confirmation',
        'requires_action',
        'processing',
    ];

    /**
     * Retourne l'intent déjà rattaché à l'achat s'il est encore utilisable, en
     * réalignant son montant si le prix a changé entre-temps. Renvoie null
     * quand il faut en créer un nouveau.
     */
    public function reusable(?string $paymentIntentId, int $amountCents): ?PaymentIntent
    {
        if ($paymentIntentId === null) {
            return null;
        }

        $intent = $this->retrieve($paymentIntentId);

        if ($intent === null || ! in_array($intent->status, self::REUSABLE_STATUSES, true)) {
            return null;
        }

        if ($intent->amount !== $amountCents && str_starts_with($intent->status, 'requires_')) {
            return $this->updateAmount($intent->id, $amountCents);
        }

        return $intent;
    }

    public function retrieve(string $paymentIntentId): ?PaymentIntent
    {
        try {
            return Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->create($params);
    }

    public function updateAmount(string $paymentIntentId, int $amountCents): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->update($paymentIntentId, ['amount' => $amountCents]);
    }

    public function refund(string $paymentIntentId): void
    {
        Cashier::stripe()->refunds->create(['payment_intent' => $paymentIntentId]);
    }

    /**
     * Rembourse sans laisser l'échec interrompre l'appelant : un remboursement
     * raté doit être signalé et traité à la main, pas faire échouer le
     * traitement d'un webhook qui, lui, a bien abouti. Renvoie false si Stripe
     * a refusé.
     */
    public function refundQuietly(string $paymentIntentId): bool
    {
        try {
            $this->refund($paymentIntentId);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Remboursement Stripe impossible : à traiter dans le dashboard.', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return false;
        }
    }
}
