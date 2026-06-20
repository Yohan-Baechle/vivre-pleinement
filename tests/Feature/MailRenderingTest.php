<?php

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentFollowUp;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentReminder;
use App\Mail\AppointmentRescheduled;
use App\Mail\AppointmentSlotUnavailable;
use App\Mail\ContactMessage;
use App\Models\Appointment;
use App\Models\AppointmentService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function renderableAppointment(array $attributes = []): Appointment
{
    $service = AppointmentService::factory()->create(['name' => 'Accompagnement ACT']);

    return Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
    ] + $attributes);
}

it('renders the confirmation mail for a confirmed appointment', function () {
    $mailable = new AppointmentConfirmation(renderableAppointment());

    $mailable->assertHasSubject('Votre rendez-vous est confirmé');
    $mailable->assertSeeInHtml('Accompagnement ACT');
});

it('renders the confirmation mail for a pending appointment', function () {
    $mailable = new AppointmentConfirmation(renderableAppointment(['status' => AppointmentStatus::Pending]));

    $mailable->assertHasSubject('Votre demande de rendez-vous a bien été reçue');
    $mailable->assertSeeInHtml('en attente de confirmation');
});

it('renders the notification mail with a reply-to address', function () {
    $appointment = renderableAppointment();
    $mailable = new AppointmentNotification($appointment);

    $mailable->assertHasReplyTo($appointment->customer_email);
    $mailable->assertSeeInHtml('Nouvelle réservation');
});

it('renders the cancelled mail for both recipients', function () {
    $appointment = renderableAppointment();

    (new AppointmentCancelled($appointment))->assertSeeInHtml('Reprendre rendez-vous');
    (new AppointmentCancelled($appointment, forAdmin: true))->assertSeeInHtml('annulé par le client');
});

it('renders the rescheduled mail with both slots for both recipients', function () {
    $start = CarbonImmutable::create(2026, 6, 25, 10, 30);
    $appointment = renderableAppointment(['starts_at' => $start, 'ends_at' => $start->addMinutes(45)]);
    $previousStart = CarbonImmutable::create(2026, 6, 20, 14, 0);

    $client = new AppointmentRescheduled($appointment, $previousStart);
    $client->assertHasSubject('Votre rendez-vous a été déplacé');
    $client->assertSeeInHtml('à 14h00');
    $client->assertSeeInHtml('25 juin 2026');

    (new AppointmentRescheduled($appointment, $previousStart, forAdmin: true))
        ->assertHasSubject('[RDV] Reprogrammation – '.$appointment->customer_full_name);
});

it('renders the reminder mail for both lead times', function () {
    $appointment = renderableAppointment();

    (new AppointmentReminder($appointment, '24h'))->assertHasSubject('Rappel : votre rendez-vous est demain');
    (new AppointmentReminder($appointment, '1h'))->assertHasSubject('Votre rendez-vous a lieu dans 1 heure');
});

it('renders the follow-up mail', function () {
    (new AppointmentFollowUp(renderableAppointment()))
        ->assertSeeInHtml('Merci pour notre échange');
});

it('renders the slot unavailable mail with a readable time', function () {
    $start = CarbonImmutable::create(2026, 6, 20, 14, 0);
    $appointment = renderableAppointment(['starts_at' => $start, 'ends_at' => $start->addMinutes(45)]);

    $mailable = new AppointmentSlotUnavailable($appointment);

    $mailable->assertSeeInHtml('intégralement remboursé');
    $mailable->assertSeeInHtml('à 14h00');
});

it('renders the contact message mail with a reply-to address', function () {
    $mailable = new ContactMessage(
        firstName: 'Camille',
        lastName: 'Durand',
        email: 'camille@example.com',
        phone: null,
        subjectLabel: 'Question générale',
        messageBody: 'Bonjour, j\'aurais une question.',
    );

    $mailable->assertHasReplyTo('camille@example.com');
    $mailable->assertSeeInHtml('Camille');
});
