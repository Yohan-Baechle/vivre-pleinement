<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  bool  $forAdmin  Indique si cette copie est destinée à Laura (plutôt qu'au client).
     */
    public function __construct(
        public Appointment $appointment,
        public CarbonInterface $previousStart,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? '[RDV] Reprogrammation – '.$this->appointment->customer_full_name
                : 'Votre rendez-vous a été déplacé',
        );
    }

    public function content(): Content
    {
        $this->appointment->loadMissing('service');

        return new Content(markdown: 'mail.appointment-rescheduled');
    }
}
