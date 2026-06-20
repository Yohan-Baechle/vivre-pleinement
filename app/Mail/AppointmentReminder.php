<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $when  '24h' ou '1h' – détermine la formulation.
     */
    public function __construct(
        public Appointment $appointment,
        public string $when,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->when === '1h'
                ? 'Votre rendez-vous a lieu dans 1 heure'
                : 'Rappel : votre rendez-vous est demain',
        );
    }

    public function content(): Content
    {
        $this->appointment->loadMissing('service');

        return new Content(markdown: 'mail.appointment-reminder');
    }
}
