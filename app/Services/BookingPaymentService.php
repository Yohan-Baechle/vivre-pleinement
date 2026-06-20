<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentSlotUnavailable;
use App\Models\Appointment;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;

class BookingPaymentService
{
    public function __construct(
        private AppointmentSlotService $slots,
    ) {}

    /**
     * Crée un PaymentIntent Stripe pour un rendez-vous. Le client_secret retourné
     * alimente le Payment Element affiché sur le site (carte + PayPal). Le
     * rendez-vous est confirmé plus tard par le webhook payment_intent.succeeded.
     *
     * automatic_payment_methods affiche les moyens activés dans le dashboard
     * Stripe (carte, PayPal…) sans liste figée.
     */
    public function createPaymentIntent(Appointment $appointment): PaymentIntent
    {
        $appointment->loadMissing('service');

        return Cashier::stripe()->paymentIntents->create([
            'amount' => $appointment->price_cents,
            'currency' => config('cashier.currency', 'eur'),
            'description' => $appointment->service->name.' – '.$appointment->starts_at->locale('fr')->isoFormat('D MMMM YYYY à H\hi'),
            'receipt_email' => $appointment->customer_email,
            'metadata' => ['appointment_id' => $appointment->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    /**
     * Marque un rendez-vous comme payé et confirmé, puis notifie les deux parties.
     * Idempotent : un webhook dupliqué pour un rendez-vous déjà payé ne fait rien.
     * Si le créneau a été pris pendant le paiement, rembourse et s'excuse à la place.
     *
     * @param  string|null  $paymentIntentId  identifiant du PaymentIntent Stripe, pour rembourser en cas de conflit.
     */
    public function fulfill(Appointment $appointment, ?string $paymentIntentId = null): void
    {
        if ($appointment->payment_status === PaymentStatus::Paid) {
            return;
        }

        $appointment->loadMissing('service');

        if ($this->slots->hasConflictingAppointment($appointment)) {
            $this->refundAndApologise($appointment, $paymentIntentId);

            return;
        }

        $appointment->update([
            'payment_status' => PaymentStatus::Paid,
            'status' => $appointment->service->requires_confirmation
                ? AppointmentStatus::Pending
                : AppointmentStatus::Confirmed,
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentConfirmation($appointment->fresh('service')));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new AppointmentNotification($appointment->fresh('service')));
    }

    private function refundAndApologise(Appointment $appointment, ?string $paymentIntentId): void
    {
        $refunded = false;

        if ($paymentIntentId !== null) {
            try {
                Cashier::stripe()->refunds->create(['payment_intent' => $paymentIntentId]);
                $refunded = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'payment_status' => $refunded ? PaymentStatus::Refunded : PaymentStatus::Paid,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentSlotUnavailable($appointment->fresh('service'), $refunded));
    }
}
