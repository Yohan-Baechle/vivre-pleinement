<?php

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Livewire\BookingCalendar;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Services\AppointmentSlotService;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function bookableService(array $attributes = []): AppointmentService
{
    $service = AppointmentService::factory()->create(array_merge([
        'duration_minutes' => 30,
        'min_notice_hours' => 12,
        'price_cents' => 0, // gratuit par défaut : ces tests ne passent pas par Stripe
        'is_active' => true,
    ], $attributes));

    // Disponibilité tous les jours, large fenêtre, pour garantir un créneau futur.
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

function futureSlot(): CarbonImmutable
{
    // 3 jours plus tard à 10:00 : hors fenêtre de min-notice.
    return CarbonImmutable::now()->addDays(3)->setTime(10, 0);
}

it('shows the booking index with active services', function () {
    bookableService(['name' => 'Accompagnement ACT']);

    $this->get(route('booking.index'))
        ->assertOk()
        ->assertSee('Accompagnement ACT')
        ->assertSee('Par téléphone')
        ->assertSee('En visio')
        ->assertSee('images/consultation-telephone-400.webp')
        ->assertSee('images/consultation-visio-400.webp')
        ->assertSee('data-booking-cta', false);
});

it('shows the booking FAQ on the index page', function () {
    bookableService();

    $this->get(route('booking.index'))
        ->assertOk()
        ->assertSee('Questions fréquentes')
        ->assertSee('Comment cela va-t-il se passer pour prendre rendez-vous ?')
        ->assertSee('FAQPage', false);
});

it('shows the service booking page', function () {
    $service = bookableService();

    $this->get(route('booking.show', $service->slug))
        ->assertOk()
        ->assertSeeLivewire(BookingCalendar::class);
});

it('jumps to the first month that has availability', function () {
    // Service disponible uniquement le dimanche : on garantit qu'au moins
    // un mois affiché contient des dispos plutôt qu'une grille vide.
    $service = AppointmentService::factory()->create(['duration_minutes' => 30, 'min_notice_hours' => 12]);
    Availability::create([
        'appointment_service_id' => null,
        'day_of_week' => 0,
        'start_time' => '09:00',
        'end_time' => '12:00',
        'is_active' => true,
    ]);

    $component = Livewire::test(BookingCalendar::class, ['service' => $service]);

    $year = $component->get('year');
    $month = $component->get('month');
    $days = app(AppointmentSlotService::class)->availableDaysForMonth($service, $year, $month);

    expect($days)->not->toBeEmpty();
});

it('returns 404 for an inactive service', function () {
    $service = bookableService(['is_active' => false]);

    $this->get(route('booking.show', $service->slug))->assertNotFound();
});

it('creates a confirmed appointment and sends both emails', function () {
    Mail::fake();
    $service = bookableService(['requires_confirmation' => false]);
    $slot = futureSlot();

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('channel', 'phone')
        ->set('consent', true)
        ->call('book')
        ->assertRedirect();

    $appointment = Appointment::query()->firstOrFail();
    expect($appointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->customer_first_name)->toBe('Camille')
        ->and($appointment->channel)->toBe(AppointmentChannel::Phone)
        ->and($appointment->token)->not->toBeNull();

    Mail::assertQueued(AppointmentConfirmation::class);
    Mail::assertQueued(AppointmentNotification::class);
});

it('applies the default meeting url from settings', function () {
    Mail::fake();
    Settings::set('meet_url', 'https://meet.google.com/abc-defg-hij');
    $service = bookableService();
    $slot = futureSlot();

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertRedirect();

    expect(Appointment::query()->firstOrFail()->meeting_url)->toBe('https://meet.google.com/abc-defg-hij');
});

it('creates a pending appointment when the service requires confirmation', function () {
    Mail::fake();
    $service = bookableService(['requires_confirmation' => true]);
    $slot = futureSlot();

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Léa')
        ->set('email', 'lea@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertRedirect();

    expect(Appointment::query()->firstOrFail()->status)->toBe(AppointmentStatus::Pending);
});

it('serves an ics calendar file for the appointment', function () {
    $service = bookableService();
    $appointment = Appointment::factory()->create(['appointment_service_id' => $service->id]);

    $this->get(route('booking.ics', $appointment->reference))
        ->assertOk()
        ->assertHeader('content-type', 'text/calendar; charset=UTF-8')
        ->assertSee('BEGIN:VEVENT', escape: false)
        ->assertSee($appointment->reference, escape: false);
});

it('requires consent', function () {
    $service = bookableService();
    $slot = futureSlot();

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', false)
        ->call('book')
        ->assertHasErrors(['consent']);

    expect(Appointment::query()->count())->toBe(0);
});

it('rejects an unavailable slot', function () {
    Mail::fake();
    $service = bookableService();
    // Créneau dans le passé / hors fenêtre.
    $badSlot = CarbonImmutable::now()->subDay()->setTime(10, 0);

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $badSlot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertHasErrors(['selectedSlot']);

    expect(Appointment::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rejects a slot that was just booked by someone else', function () {
    Mail::fake();
    $service = bookableService();
    $slot = futureSlot();

    // Quelqu'un a déjà pris ce créneau.
    Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'starts_at' => $slot,
        'ends_at' => $slot->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertHasErrors(['selectedSlot']);

    expect(Appointment::query()->count())->toBe(1);
});
