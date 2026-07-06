<?php

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
use Illuminate\Support\Facades\Mail;

it('sends every appointment and contact mailable preview to the default address', function () {
    Mail::fake();

    $this->artisan('mail:preview')->assertSuccessful();

    Mail::assertSentCount(14);

    foreach ([
        AppointmentConfirmation::class,
        AppointmentNotification::class,
        AppointmentReminder::class,
        AppointmentRescheduled::class,
        AppointmentCancelled::class,
        AppointmentSlotUnavailable::class,
        AppointmentCheckoutExpired::class,
        AppointmentNoShow::class,
        AppointmentFollowUp::class,
        ContactMessage::class,
    ] as $mailable) {
        Mail::assertSent($mailable, fn ($mail) => $mail->hasTo('preview@vivre-pleinement.test'));
    }

    Mail::assertSent(AppointmentConfirmation::class, 2);
    Mail::assertSent(AppointmentReminder::class, 2);
    Mail::assertSent(AppointmentRescheduled::class, 2);
    Mail::assertSent(AppointmentCancelled::class, 2);
});

it('sends the previews to a custom address when provided', function () {
    Mail::fake();

    $this->artisan('mail:preview', ['--email' => 'demo@example.test'])->assertSuccessful();

    Mail::assertSent(ContactMessage::class, fn ($mail) => $mail->hasTo('demo@example.test'));
});
