<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentCheckoutExpired;
use App\Mail\AppointmentFollowUp;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Settings::flush();
    Settings::setMany([
        'reminder_24h_enabled' => '1',
        'reminder_1h_enabled' => '1',
        'followup_enabled' => '1',
    ]);
});

function appointmentAt(CarbonImmutable $start, array $attributes = []): Appointment
{
    $service = AppointmentService::factory()->create(['duration_minutes' => 30]);

    return Appointment::factory()->create(array_merge([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ], $attributes));
}

it('sends a 24h reminder for appointments tomorrow', function () {
    Mail::fake();
    $appointment = appointmentAt(CarbonImmutable::now()->addHours(24));

    $this->artisan('appointments:send-reminders')->assertOk();

    Mail::assertQueued(AppointmentReminder::class, fn ($mail) => $mail->when === '24h');
    expect($appointment->fresh()->reminded_24h_at)->not->toBeNull();
});

it('sends a 1h reminder for imminent appointments', function () {
    Mail::fake();
    $appointment = appointmentAt(CarbonImmutable::now()->addMinutes(45));

    $this->artisan('appointments:send-reminders')->assertOk();

    Mail::assertQueued(AppointmentReminder::class, fn ($mail) => $mail->when === '1h');
    expect($appointment->fresh()->reminded_1h_at)->not->toBeNull();
});

it('is idempotent — a second run sends nothing new', function () {
    Mail::fake();
    appointmentAt(CarbonImmutable::now()->addHours(24));

    $this->artisan('appointments:send-reminders');
    Mail::assertQueued(AppointmentReminder::class, 1);

    $this->artisan('appointments:send-reminders');
    // Toujours 1 au total : le 2e run n'envoie rien.
    Mail::assertQueued(AppointmentReminder::class, 1);
});

it('respects the disabled toggle', function () {
    Mail::fake();
    Settings::set('reminder_24h_enabled', '0');
    appointmentAt(CarbonImmutable::now()->addHours(24));

    $this->artisan('appointments:send-reminders');

    Mail::assertNotQueued(AppointmentReminder::class);
});

it('sends a follow-up and marks completed after the appointment', function () {
    Mail::fake();
    $past = CarbonImmutable::now()->subHours(2);
    $appointment = appointmentAt($past->subMinutes(30), ['starts_at' => $past->subMinutes(30), 'ends_at' => $past]);

    $this->artisan('appointments:send-reminders');

    Mail::assertQueued(AppointmentFollowUp::class);
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Completed)
        ->and($appointment->fresh()->followed_up_at)->not->toBeNull();
});

it('does not remind appointments outside the window', function () {
    Mail::fake();
    appointmentAt(CarbonImmutable::now()->addDays(5));

    $this->artisan('appointments:send-reminders');

    Mail::assertNotQueued(AppointmentReminder::class);
});

it('cancels a stale unpaid checkout and emails the client', function () {
    Mail::fake();
    $appointment = appointmentAt(CarbonImmutable::now()->addDays(3), [
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'created_at' => CarbonImmutable::now()->subMinutes(31),
    ]);

    $this->artisan('appointments:send-reminders');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and($appointment->fresh()->cancelled_at)->not->toBeNull();
    Mail::assertQueued(AppointmentCheckoutExpired::class);
});

it('leaves a recent unpaid checkout alone', function () {
    Mail::fake();
    $appointment = appointmentAt(CarbonImmutable::now()->addDays(3), [
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'created_at' => CarbonImmutable::now()->subMinutes(10),
    ]);

    $this->artisan('appointments:send-reminders');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Pending);
    Mail::assertNotQueued(AppointmentCheckoutExpired::class);
});
