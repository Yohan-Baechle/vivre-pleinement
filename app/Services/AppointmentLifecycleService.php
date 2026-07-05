<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Support\SiteContact;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

/**
 * Transitions de statut d'un rendez-vous partagées entre le site public et
 * l'admin Filament, pour que statut, horodatage et notifications restent
 * identiques quel que soit le point d'entrée. La confirmation après paiement
 * vit dans BookingPaymentService car elle porte aussi le statut de paiement.
 */
class AppointmentLifecycleService
{
    public function confirm(Appointment $appointment): void
    {
        $appointment->update(['status' => AppointmentStatus::Confirmed]);

        Mail::to($appointment->customer_email)->send(new AppointmentConfirmation($appointment->fresh('service')));
    }

    public function cancel(Appointment $appointment): void
    {
        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentCancelled($appointment));
        Mail::to(SiteContact::notifyEmail())
            ->send(new AppointmentCancelled($appointment, forAdmin: true));
    }
}
