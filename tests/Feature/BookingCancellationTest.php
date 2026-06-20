<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Livewire\BookingCalendar;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function serviceWithDailyAvailability(): AppointmentService
{
    $service = AppointmentService::factory()->create(['duration_minutes' => 30, 'min_notice_hours' => 12]);
    foreach (range(0, 6) as $dow) {
        Availability::create([
            'appointment_service_id' => null,
            'day_of_week' => $dow,
            'start_time' => '08:00',
            'end_time' => '20:00',
            'is_active' => true,
        ]);
    }

    return $service;
}

function futureAppointment(AppointmentService $service, ?CarbonImmutable $start = null): Appointment
{
    $start ??= CarbonImmutable::now()->addDays(3)->setTime(10, 0);

    return Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);
}

it('shows the manage page via token', function () {
    $appointment = futureAppointment(serviceWithDailyAvailability());

    $this->get(route('booking.manage', $appointment->token))
        ->assertOk()
        ->assertSee($appointment->reference, escape: false);
});

it('cancels an appointment and frees the slot', function () {
    Mail::fake();
    $service = serviceWithDailyAvailability();
    $start = CarbonImmutable::now()->addDays(3)->setTime(10, 0);
    $appointment = futureAppointment($service, $start);

    // Le créneau est occupé avant annulation.
    expect(app(AppointmentSlotService::class)->isSlotBookable($service, $start))->toBeFalse();

    $this->post(route('booking.cancel', $appointment->token))
        ->assertRedirect(route('booking.manage', $appointment->token));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and(app(AppointmentSlotService::class)->isSlotBookable($service, $start))->toBeTrue();

    Mail::assertQueued(AppointmentCancelled::class, 2); // client + admin
});

it('refuses to cancel a past appointment', function () {
    $service = serviceWithDailyAvailability();
    $past = CarbonImmutable::now()->subDay()->setTime(10, 0);
    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'starts_at' => $past,
        'ends_at' => $past->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $this->post(route('booking.cancel', $appointment->token))->assertForbidden();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('refuses to reserve a slot already taken (atomic guard)', function () {
    $service = serviceWithDailyAvailability();
    $start = CarbonImmutable::now()->addDays(3)->setTime(10, 0);

    // Premier RDV occupe le créneau.
    futureAppointment($service, $start);

    // La réservation atomique doit refuser le même créneau.
    $second = app(AppointmentSlotService::class)->reserve($service, $start, [
        'reference' => Appointment::generateReference(),
        'token' => Appointment::generateToken(),
        'customer_first_name' => 'Autre',
        'customer_email' => 'autre@gmail.com',
        'status' => AppointmentStatus::Confirmed,
        'price_cents' => 0,
        'payment_status' => PaymentStatus::NotRequired,
    ]);

    expect($second)->toBeNull()
        ->and(Appointment::query()->count())->toBe(1);
});

it('reschedules an appointment without creating a new one', function () {
    Mail::fake();
    $service = serviceWithDailyAvailability();
    $appointment = futureAppointment($service);

    $newStart = CarbonImmutable::now()->addDays(5)->setTime(14, 0);

    Livewire::test(BookingCalendar::class, ['service' => $service, 'rescheduleToken' => $appointment->token])
        ->set('selectedSlot', $newStart->toIso8601String())
        ->call('book')
        ->assertRedirect(route('booking.manage', $appointment->token));

    expect(Appointment::query()->count())->toBe(1)
        ->and($appointment->fresh()->starts_at->equalTo($newStart))->toBeTrue();

    Mail::assertQueued(AppointmentRescheduled::class, 2); // client + admin
    Mail::assertNotQueued(AppointmentConfirmation::class);
});
