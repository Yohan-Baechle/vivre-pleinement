<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCheckoutExpired extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation n\'a pas été finalisée',
        );
    }

    public function content(): Content
    {
        $this->appointment->loadMissing('service');

        return new Content(markdown: 'mail.appointment-checkout-expired');
    }
}
