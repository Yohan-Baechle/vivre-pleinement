<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentSlotUnavailable;
use App\Models\Appointment;
use App\Support\SiteContact;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;

class BookingPaymentService
{
    public function __construct(
        private AppointmentSlotService $slots,
        private StripePaymentIntents $intents,
    ) {}

    /**
     * Retourne le PaymentIntent du rendez-vous : réutilise celui déjà créé
     * (rafraîchissement, second onglet) tant qu'il n'est pas finalisé, sinon en
     * crée un nouveau. Le client_secret retourné alimente le Payment Element
     * (carte + PayPal). Le rendez-vous est confirmé plus tard par le webhook
     * payment_intent.succeeded.
     *
     * automatic_payment_methods affiche les moyens activés dans le dashboard
     * Stripe (carte, PayPal…) sans liste figée.
     */
    public function createPaymentIntent(Appointment $appointment): PaymentIntent
    {
        $appointment->loadMissing('service');

        $existing = $this->intents->reusable(
            $appointment->stripe_payment_intent_id,
            $appointment->price_cents,
        );

        if ($existing !== null) {
            return $existing;
        }

        $intent = $this->intents->create([
            'amount' => $appointment->price_cents,
            'currency' => config('cashier.currency', 'eur'),
            'description' => $appointment->service->name.' – '.$appointment->starts_at->isoFormat('D MMMM YYYY à H\hi'),
            'receipt_email' => $appointment->customer_email,
            'metadata' => ['appointment_id' => $appointment->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $appointment->update(['stripe_payment_intent_id' => $intent->id]);

        return $intent;
    }

    /**
     * Marque un rendez-vous comme payé et confirmé, puis notifie les deux
     * parties. Idempotent : un webhook dupliqué pour un rendez-vous déjà payé
     * ne fait rien, un paiement concurrent (intent différent) est remboursé
     * d'office. Verrouille la ligne le temps de la vérification pour empêcher
     * deux workers de traiter le même webhook en double. Si le créneau a été
     * pris pendant le paiement, rembourse et s'excuse à la place.
     *
     * Seules la vérification et la mise à jour du statut sont verrouillées :
     * les appels réseau Stripe (remboursement) et l'envoi des mails se font
     * après le COMMIT, pour ne jamais faire lire à un job de mail en file un
     * enregistrement pas encore validé.
     *
     * @param  string|null  $paymentIntentId  PaymentIntent Stripe, pour
     *                                        rembourser en cas de conflit.
     */
    public function fulfill(Appointment $appointment, ?string $paymentIntentId = null): void
    {
        [$outcome, $locked] = DB::transaction(function () use ($appointment, $paymentIntentId) {
            /** @var Appointment $locked */
            $locked = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status === PaymentStatus::Paid) {
                return ['duplicate', $locked];
            }

            $locked->loadMissing('service');

            if ($this->slots->hasConflictingAppointment($locked)) {
                $locked->update([
                    'status' => AppointmentStatus::Cancelled,
                    'cancelled_at' => CarbonImmutable::now(),
                ]);

                return ['conflict', $locked];
            }

            $locked->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => $locked->service->requires_confirmation
                    ? AppointmentStatus::Pending
                    : AppointmentStatus::Confirmed,
                'stripe_payment_intent_id' => $paymentIntentId ?? $locked->stripe_payment_intent_id,
            ]);

            return ['fulfilled', $locked];
        });

        match ($outcome) {
            'duplicate' => $this->refundDuplicatePayment($locked, $paymentIntentId),
            'conflict' => $this->refundAndApologise($locked, $paymentIntentId),
            'fulfilled' => $this->sendConfirmation($locked),
        };
    }

    private function sendConfirmation(Appointment $appointment): void
    {
        Mail::to($appointment->customer_email)->send(new AppointmentConfirmation($appointment->fresh('service')));
        Mail::to(SiteContact::notifyEmail())
            ->send(new AppointmentNotification($appointment->fresh('service')));
    }

    /**
     * Un paiement réussi arrive pour un rendez-vous déjà payé via un intent
     * différent : le client a payé deux fois (deux onglets avant la
     * réutilisation d'intent). On rembourse le second débit.
     */
    private function refundDuplicatePayment(Appointment $appointment, ?string $paymentIntentId): void
    {
        if ($paymentIntentId === null || $paymentIntentId === $appointment->stripe_payment_intent_id) {
            return;
        }

        if ($this->intents->refundQuietly($paymentIntentId)) {
            Log::warning('Second paiement détecté pour un rendez-vous déjà payé : remboursé automatiquement.', [
                'appointment_id' => $appointment->id,
                'kept_payment_intent_id' => $appointment->stripe_payment_intent_id,
                'refunded_payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        Log::error('Second paiement détecté pour un rendez-vous déjà payé mais remboursement impossible.', [
            'appointment_id' => $appointment->id,
            'payment_intent_id' => $paymentIntentId,
        ]);
    }

    /**
     * Enregistre qu'un rendez-vous payé a été remboursé (webhook
     * charge.refunded ou action admin). Idempotent : un rendez-vous déjà
     * remboursé ou jamais payé ne bouge pas.
     *
     * Le statut du rendez-vous lui-même n'est pas touché : rembourser n'est
     * pas annuler. Une séance honorée puis remboursée par geste commercial
     * doit rester au planning, et libérer le créneau enverrait des emails
     * d'annulation que personne n'a demandés. L'annulation reste une action
     * explicite.
     */
    /**
     * Enregistre qu'un remboursement a eu lieu, sans rien demander à Stripe.
     *
     * C'est la voie du webhook charge.refunded, qui arrive précisément parce
     * que Stripe a déjà crédité le client : y déclencher un remboursement en
     * émettrait un second.
     */
    public function refund(Appointment $appointment): void
    {
        if ($appointment->payment_status !== PaymentStatus::Paid) {
            return;
        }

        $appointment->update(['payment_status' => PaymentStatus::Refunded]);

        Log::info('Rendez-vous remboursé.', [
            'appointment_id' => $appointment->id,
            'reference' => $appointment->reference,
        ]);
    }

    /**
     * Émet le remboursement chez Stripe, puis seulement s'il a abouti,
     * l'enregistre. L'ordre importe : l'inverse laisserait un rendez-vous
     * affiché « remboursé » que Stripe n'a jamais crédité, ce qui ne se
     * découvrirait qu'à la réclamation du client.
     *
     * C'est la voie de l'administration, à l'inverse de refund() qui ne fait
     * qu'acter un remboursement décidé ailleurs.
     *
     * @return bool false si rien n'a été remboursé
     */
    public function issueRefund(Appointment $appointment): bool
    {
        if ($appointment->payment_status !== PaymentStatus::Paid) {
            return false;
        }

        $paymentIntentId = $appointment->stripe_payment_intent_id;

        if ($paymentIntentId === null) {
            Log::warning('Remboursement demandé sur un rendez-vous sans paiement Stripe.', [
                'appointment_id' => $appointment->id,
                'reference' => $appointment->reference,
            ]);

            return false;
        }

        if (! $this->intents->refundQuietly($paymentIntentId)) {
            return false;
        }

        $this->refund($appointment);

        return true;
    }

    private function refundAndApologise(Appointment $appointment, ?string $paymentIntentId): void
    {
        $refunded = $paymentIntentId !== null && $this->intents->refundQuietly($paymentIntentId);

        $appointment->update([
            'payment_status' => $refunded ? PaymentStatus::Refunded : PaymentStatus::Paid,
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentSlotUnavailable($appointment->fresh('service'), $refunded));
    }
}
