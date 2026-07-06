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
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;
use Throwable;

class BookingPaymentService
{
    /**
     * Statuts Stripe pour lesquels un PaymentIntent existant peut resservir au
     * Payment Element au lieu d'en créer un nouveau (risque de double débit sinon).
     *
     * @var list<string>
     */
    private const REUSABLE_INTENT_STATUSES = [
        'requires_payment_method',
        'requires_confirmation',
        'requires_action',
        'processing',
    ];

    public function __construct(
        private AppointmentSlotService $slots,
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

        $existing = $this->reusableIntentFor($appointment);

        if ($existing !== null) {
            return $existing;
        }

        $intent = $this->createIntent([
            'amount' => $appointment->price_cents,
            'currency' => config('cashier.currency', 'eur'),
            'description' => $appointment->service->name.' – '.$appointment->starts_at->locale('fr')->isoFormat('D MMMM YYYY à H\hi'),
            'receipt_email' => $appointment->customer_email,
            'metadata' => ['appointment_id' => $appointment->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $appointment->update(['stripe_payment_intent_id' => $intent->id]);

        return $intent;
    }

    /**
     * Récupère le PaymentIntent déjà rattaché au rendez-vous s'il est encore
     * utilisable, en réalignant son montant si le prix a changé entre-temps.
     */
    private function reusableIntentFor(Appointment $appointment): ?PaymentIntent
    {
        if ($appointment->stripe_payment_intent_id === null) {
            return null;
        }

        $intent = $this->retrieveIntent($appointment->stripe_payment_intent_id);

        if ($intent === null || ! in_array($intent->status, self::REUSABLE_INTENT_STATUSES, true)) {
            return null;
        }

        if ($intent->amount !== $appointment->price_cents && str_starts_with($intent->status, 'requires_')) {
            return $this->updateIntentAmount($intent->id, $appointment->price_cents);
        }

        return $intent;
    }

    public function retrieveIntent(string $paymentIntentId): ?PaymentIntent
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
    public function createIntent(array $params): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->create($params);
    }

    public function updateIntentAmount(string $paymentIntentId, int $amountCents): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->update($paymentIntentId, ['amount' => $amountCents]);
    }

    /**
     * Marque un rendez-vous comme payé et confirmé, puis notifie les deux parties.
     * Idempotent : un webhook dupliqué pour un rendez-vous déjà payé ne fait rien,
     * un paiement concurrent (intent différent) est remboursé d'office. Verrouille
     * la ligne le temps de la vérification pour empêcher deux workers de traiter
     * le même webhook en double.
     * Si le créneau a été pris pendant le paiement, rembourse et s'excuse à la place.
     *
     * @param  string|null  $paymentIntentId  identifiant du PaymentIntent Stripe, pour rembourser en cas de conflit.
     */
    public function fulfill(Appointment $appointment, ?string $paymentIntentId = null): void
    {
        // Seule la vérification + la mise à jour du statut sont verrouillées : les
        // appels réseau Stripe (remboursement) et l'envoi des mails se font après
        // la validation (COMMIT) de la transaction, pour ne jamais faire lire à un
        // job de mail en file un enregistrement pas encore validé.
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
     * différent : le client a payé deux fois (deux onglets avant la réutilisation
     * d'intent). On rembourse le second débit.
     */
    private function refundDuplicatePayment(Appointment $appointment, ?string $paymentIntentId): void
    {
        if ($paymentIntentId === null || $paymentIntentId === $appointment->stripe_payment_intent_id) {
            return;
        }

        try {
            $this->refundPaymentIntent($paymentIntentId);

            Log::warning('Second paiement détecté pour un rendez-vous déjà payé : remboursé automatiquement.', [
                'appointment_id' => $appointment->id,
                'kept_payment_intent_id' => $appointment->stripe_payment_intent_id,
                'refunded_payment_intent_id' => $paymentIntentId,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Second paiement détecté mais remboursement automatique impossible : à traiter dans le dashboard Stripe.', [
                'appointment_id' => $appointment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);
        }
    }

    private function refundAndApologise(Appointment $appointment, ?string $paymentIntentId): void
    {
        $refunded = false;

        if ($paymentIntentId !== null) {
            try {
                $this->refundPaymentIntent($paymentIntentId);
                $refunded = true;
            } catch (Throwable $e) {
                report($e);
            }
        }

        $appointment->update([
            'payment_status' => $refunded ? PaymentStatus::Refunded : PaymentStatus::Paid,
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentSlotUnavailable($appointment->fresh('service'), $refunded));
    }

    public function refundPaymentIntent(string $paymentIntentId): void
    {
        Cashier::stripe()->refunds->create(['payment_intent' => $paymentIntentId]);
    }
}
