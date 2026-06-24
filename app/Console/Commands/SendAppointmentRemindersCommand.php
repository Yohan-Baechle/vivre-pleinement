<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentCheckoutExpired;
use App\Mail\AppointmentFollowUp;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('appointments:send-reminders')]
#[Description('Envoie les rappels (24h, 1h) et messages de suivi des rendez-vous confirmés.')]
class SendAppointmentRemindersCommand extends Command
{
    public function handle(): int
    {
        $now = CarbonImmutable::now();
        $sent = 0;

        if (Settings::boolean('reminder_24h_enabled', true)) {
            $sent += $this->sendReminders('24h', $now->addHours(23), $now->addHours(25), 'reminded_24h_at');
        }

        if (Settings::boolean('reminder_1h_enabled', true)) {
            $sent += $this->sendReminders('1h', $now, $now->addMinutes(70), 'reminded_1h_at');
        }

        if (Settings::boolean('followup_enabled', true)) {
            $sent += $this->sendFollowUps($now);
        }

        $expired = $this->sweepStaleCheckouts($now);

        $this->info("Rappels/suivis envoyés : {$sent}. Réservations non payées expirées : {$expired}.");

        return self::SUCCESS;
    }

    /**
     * Envoie un type de rappel sur la fenêtre donnée, avec un claim atomique :
     * seul le process qui passe le flag de null à maintenant envoie le mail.
     */
    private function sendReminders(string $when, CarbonImmutable $from, CarbonImmutable $to, string $flag): int
    {
        $count = 0;

        Appointment::query()
            ->with('service')
            ->where('status', AppointmentStatus::Confirmed)
            ->whereNull($flag)
            ->whereBetween('starts_at', [$from, $to])
            ->get()
            ->each(function (Appointment $appointment) use ($when, $flag, &$count) {
                $claimed = Appointment::query()
                    ->whereKey($appointment->getKey())
                    ->whereNull($flag)
                    ->update([$flag => CarbonImmutable::now()]);

                if ($claimed === 1) {
                    Mail::to($appointment->customer_email)->send(new AppointmentReminder($appointment, $when));
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Libère les créneaux des réservations payantes restées "Pending/unpaid"
     * (checkout Stripe abandonné) au-delà du TTL de la session (~30 min).
     */
    private function sweepStaleCheckouts(CarbonImmutable $now): int
    {
        $count = 0;

        Appointment::query()
            ->with('service')
            ->where('status', AppointmentStatus::Pending)
            ->where('payment_status', PaymentStatus::Unpaid)
            ->where('created_at', '<', $now->subMinutes(30))
            ->get()
            ->each(function (Appointment $appointment) use ($now, &$count) {
                $appointment->forceFill([
                    'status' => AppointmentStatus::Cancelled,
                    'cancelled_at' => $now,
                ])->save();

                Mail::to($appointment->customer_email)
                    ->send(new AppointmentCheckoutExpired($appointment));

                $count++;
            });

        return $count;
    }

    private function sendFollowUps(CarbonImmutable $now): int
    {
        $count = 0;

        Appointment::query()
            ->with('service')
            ->where('status', AppointmentStatus::Confirmed)
            ->whereNull('followed_up_at')
            ->where('ends_at', '<=', $now)
            ->get()
            ->each(function (Appointment $appointment) use (&$count) {
                Mail::to($appointment->customer_email)->send(new AppointmentFollowUp($appointment));
                $appointment->forceFill([
                    'status' => AppointmentStatus::Completed,
                    'followed_up_at' => CarbonImmutable::now(),
                ])->save();
                $count++;
            });

        return $count;
    }
}
