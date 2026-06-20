<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  bool  $forAdmin  Indique si cette copie est destinée à Laura (plutôt qu'au client).
     */
    public function __construct(
        public Appointment $appointment,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? '[RDV] Annulation – '.$this->appointment->customer_full_name
                : 'Votre rendez-vous a été annulé',
        );
    }

    public function content(): Content
    {
        $this->appointment->loadMissing('service');

        return new Content(markdown: 'mail.appointment-cancelled');
    }
}
