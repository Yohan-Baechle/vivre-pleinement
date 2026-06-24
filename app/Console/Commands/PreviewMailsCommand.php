<?php

namespace App\Console\Commands;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentCheckoutExpired;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentFollowUp;
use App\Mail\AppointmentNoShow;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentReminder;
use App\Mail\AppointmentRescheduled;
use App\Mail\AppointmentSlotUnavailable;
use App\Mail\ContactMessage;
use App\Models\Appointment;
use App\Models\AppointmentService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

#[Signature('mail:preview {--email=preview@vivre-pleinement.test}')]
#[Description('Envoie chaque mail de l\'application (toutes variantes) à Mailpit pour vérifier leur rendu.')]
class PreviewMailsCommand extends Command
{
    public function handle(): int
    {
        $email = $this->option('email');

        foreach ($this->mailables() as $label => $mailable) {
            Mail::to($email)->sendNow($mailable);
            $this->line("  <fg=green>✓</> {$label}");
        }

        $this->newLine();
        $this->info("Mails envoyés à {$email}. Ouvrez Mailpit pour les consulter.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, Mailable>
     */
    protected function mailables(): array
    {
        $appointment = $this->sampleAppointment();

        return [
            'Confirmation (confirmé)' => new AppointmentConfirmation($appointment),
            'Confirmation (en attente)' => new AppointmentConfirmation($this->sampleAppointment(['status' => AppointmentStatus::Pending])),
            'Notification admin' => new AppointmentNotification($appointment),
            'Rappel 24h' => new AppointmentReminder($appointment, '24h'),
            'Rappel 1h' => new AppointmentReminder($appointment, '1h'),
            'Reprogrammation (client)' => new AppointmentRescheduled($appointment, $appointment->starts_at->copy()->subDays(2)),
            'Reprogrammation (admin)' => new AppointmentRescheduled($appointment, $appointment->starts_at->copy()->subDays(2), forAdmin: true),
            'Annulation (client)' => new AppointmentCancelled($appointment),
            'Annulation (admin)' => new AppointmentCancelled($appointment, forAdmin: true),
            'Créneau indisponible' => new AppointmentSlotUnavailable($appointment),
            'Checkout expiré (non payé)' => new AppointmentCheckoutExpired($appointment),
            'Absent (no-show)' => new AppointmentNoShow($appointment),
            'Suivi' => new AppointmentFollowUp($appointment),
            'Message de contact' => new ContactMessage(
                firstName: 'Camille',
                lastName: 'Durand',
                email: 'camille@example.com',
                phone: '06 12 34 56 78',
                subjectLabel: 'Question générale',
                messageBody: "Bonjour,\n\nJ'aurais une question concernant l'accompagnement.\n\nMerci d'avance.",
            ),
        ];
    }

    /**
     * Construit un rendez-vous en mémoire (jamais persisté) pour le rendu des aperçus.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function sampleAppointment(array $overrides = []): Appointment
    {
        $service = AppointmentService::factory()->make([
            'name' => 'Accompagnement ACT',
            'slug' => 'accompagnement-act',
            'price_cents' => 6000,
        ]);

        $start = CarbonImmutable::now()->addDay()->setTime(14, 0);

        $appointment = Appointment::factory()->make([
            'channel' => AppointmentChannel::Video,
            'status' => AppointmentStatus::Confirmed,
            'starts_at' => $start,
            'ends_at' => $start->addMinutes(45),
            'price_cents' => 6000,
            'meeting_url' => 'https://meet.example.com/laura-act',
            'token' => Appointment::generateToken(),
            ...$overrides,
        ]);

        $appointment->id = 1;
        $appointment->setRelation('service', $service);

        return $appointment;
    }
}
