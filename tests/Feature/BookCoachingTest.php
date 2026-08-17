<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Livewire\BookingCalendar;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\BookOrder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

/**
 * Prestation payante ouverte tous les jours, pour que le calendrier propose
 * toujours un créneau au test.
 */
function coachingService(): AppointmentService
{
    $service = AppointmentService::factory()->create([
        'price_cents' => 5000,
        'duration_minutes' => 60,
        'min_notice_hours' => 12,
        'requires_confirmation' => false,
    ]);

    foreach (range(0, 6) as $dow) {
        Availability::factory()->dayOfWeek($dow)->create();
    }

    return $service;
}

function firstBookableSlot(): string
{
    return CarbonImmutable::now()->addDays(3)->setTime(10, 0)->toIso8601String();
}

it('ouvre la réservation du coaching pour une commande payée avec coaching', function () {
    coachingService();
    $order = BookOrder::factory()->withCoaching()->paid()->create();

    $this->get(route('book.coaching', $order->token))
        ->assertOk()
        ->assertSee('Choisissez votre créneau');
});

it('refuse la réservation à une commande sans coaching', function () {
    coachingService();
    $order = BookOrder::factory()->paid()->create();

    $this->get(route('book.coaching', $order->token))->assertForbidden();
});

it('refuse la réservation à une commande non payée', function () {
    coachingService();
    $order = BookOrder::factory()->withCoaching()->create();

    $this->get(route('book.coaching', $order->token))->assertForbidden();
});

it('refuse la réservation après un remboursement', function () {
    coachingService();
    $order = BookOrder::factory()->withCoaching()->refunded()->create();

    $this->get(route('book.coaching', $order->token))->assertForbidden();
});

it('pré-remplit le formulaire avec les coordonnées de l\'acheteur', function () {
    $service = coachingService();
    $order = BookOrder::factory()->withCoaching()->paid()->create();

    Livewire::test(BookingCalendar::class, [
        'service' => $service,
        'bookOrderToken' => $order->token,
    ])
        ->assertSet('firstName', $order->customer_first_name)
        ->assertSet('email', $order->customer_email);
});

it('crée un rendez-vous à 0 € sans repasser par le paiement', function () {
    Mail::fake();
    $service = coachingService();
    $order = BookOrder::factory()->withCoaching()->paid()->create();

    Livewire::test(BookingCalendar::class, [
        'service' => $service,
        'bookOrderToken' => $order->token,
    ])
        ->set('selectedSlot', firstBookableSlot())
        ->set('consent', true)
        ->call('book');

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->price_cents)->toBe(0)
        ->and($appointment->payment_status)->toBe(PaymentStatus::NotRequired)
        ->and($appointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($order->fresh()->coaching_appointment_id)->toBe($appointment->id);
});

it('rend le lien de coaching inutilisable une fois consommé', function () {
    Mail::fake();
    $service = coachingService();
    $order = BookOrder::factory()->withCoaching()->paid()->create();

    Livewire::test(BookingCalendar::class, [
        'service' => $service,
        'bookOrderToken' => $order->token,
    ])
        ->set('selectedSlot', firstBookableSlot())
        ->set('consent', true)
        ->call('book');

    $order->refresh();

    expect($order->canBookCoaching())->toBeFalse();

    $this->get(route('book.coaching', $order->token))
        ->assertRedirect(route('booking.confirmation', $order->coachingAppointment->token));
});

it('facture normalement un visiteur sans jeton de commande', function () {
    Mail::fake();
    $service = coachingService();

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('firstName', 'Camille')
        ->set('lastName', 'Durand')
        ->set('email', 'camille@gmail.com')
        ->set('selectedSlot', firstBookableSlot())
        ->set('consent', true)
        ->call('book');

    expect(Appointment::query()->firstOrFail()->price_cents)->toBe(5000);
});
