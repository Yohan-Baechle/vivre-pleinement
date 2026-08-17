<?php

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseAccessGranted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Enrollment $enrollment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre accès à la formation est ouvert',
        );
    }

    public function content(): Content
    {
        $this->enrollment->loadMissing(['course', 'student']);

        return new Content(markdown: 'mail.course-access-granted');
    }
}
