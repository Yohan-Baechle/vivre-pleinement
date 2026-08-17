<?php

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\BookingPaymentService;
use App\Services\StripePaymentIntents;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Stripe\PaymentIntent;

uses(LazilyRefreshDatabase::class);

function appointmentWithIntent(?string $intentId, int $priceCents = 7000): Appointment
{
    $service = AppointmentService::factory()->create(['price_cents' => $priceCents]);

    return Appointment::factory()->pending()->create([
        'appointment_service_id' => $service->id,
        'price_cents' => $priceCents,
        'stripe_payment_intent_id' => $intentId,
    ]);
}

function fakeBookingIntent(string $id, string $status, int $amount): PaymentIntent
{
    return PaymentIntent::constructFrom([
        'id' => $id,
        'status' => $status,
        'amount' => $amount,
        'client_secret' => $id.'_secret',
    ]);
}

it('réutilise le PaymentIntent déjà créé au lieu d\'en générer un second', function () {
    $appointment = appointmentWithIntent('pi_existing');

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')
            ->once()
            ->with('pi_existing', 7000)
            ->andReturn(fakeBookingIntent('pi_existing', 'requires_payment_method', 7000));
        $mock->shouldNotReceive('create');
    });

    $intent = app(BookingPaymentService::class)->createPaymentIntent($appointment);

    expect($intent->id)->toBe('pi_existing');
});

it('transmet le prix courant du rendez-vous au calcul de réutilisation', function () {
    $appointment = appointmentWithIntent('pi_existing', priceCents: 9900);

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')
            ->once()
            ->with('pi_existing', 9900)
            ->andReturn(fakeBookingIntent('pi_existing', 'requires_payment_method', 9900));
    });

    expect(app(BookingPaymentService::class)->createPaymentIntent($appointment)->amount)->toBe(9900);
});

it('crée un nouvel intent de rendez-vous lorsqu\'aucun n\'est réutilisable', function () {
    $appointment = appointmentWithIntent('pi_finalise');

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')->once()->andReturn(null);
        $mock->shouldReceive('create')
            ->once()
            ->andReturn(fakeBookingIntent('pi_nouveau', 'requires_payment_method', 7000));
    });

    $intent = app(BookingPaymentService::class)->createPaymentIntent($appointment);

    expect($intent->id)->toBe('pi_nouveau')
        ->and($appointment->fresh()->stripe_payment_intent_id)->toBe('pi_nouveau');
});

it('crée un intent de rendez-vous et le mémorise au premier passage', function () {
    $appointment = appointmentWithIntent(null);

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')->once()->with(null, 7000)->andReturn(null);
        $mock->shouldReceive('create')
            ->once()
            ->andReturn(fakeBookingIntent('pi_premier', 'requires_payment_method', 7000));
    });

    app(BookingPaymentService::class)->createPaymentIntent($appointment);

    expect($appointment->fresh()->stripe_payment_intent_id)->toBe('pi_premier');
});
