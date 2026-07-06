<?php

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\BookingPaymentService;
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

    $service = $this->partialMock(BookingPaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->with('pi_existing')
            ->andReturn(fakeBookingIntent('pi_existing', 'requires_payment_method', 7000));
        $mock->shouldNotReceive('createIntent');
    });

    $intent = $service->createPaymentIntent($appointment);

    expect($intent->id)->toBe('pi_existing');
});

it('réaligne le montant de l\'intent existant lorsque le prix a changé', function () {
    $appointment = appointmentWithIntent('pi_existing', priceCents: 9900);

    $service = $this->partialMock(BookingPaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->andReturn(fakeBookingIntent('pi_existing', 'requires_payment_method', 7000));
        $mock->shouldReceive('updateIntentAmount')
            ->once()
            ->with('pi_existing', 9900)
            ->andReturn(fakeBookingIntent('pi_existing', 'requires_payment_method', 9900));
        $mock->shouldNotReceive('createIntent');
    });

    $intent = $service->createPaymentIntent($appointment);

    expect($intent->amount)->toBe(9900);
});

it('crée un nouvel intent de rendez-vous lorsque l\'ancien est déjà finalisé', function () {
    $appointment = appointmentWithIntent('pi_finalise');

    $service = $this->partialMock(BookingPaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->andReturn(fakeBookingIntent('pi_finalise', 'succeeded', 7000));
        $mock->shouldReceive('createIntent')
            ->once()
            ->andReturn(fakeBookingIntent('pi_nouveau', 'requires_payment_method', 7000));
    });

    $intent = $service->createPaymentIntent($appointment);

    expect($intent->id)->toBe('pi_nouveau')
        ->and($appointment->fresh()->stripe_payment_intent_id)->toBe('pi_nouveau');
});

it('crée un intent de rendez-vous et le mémorise au premier passage', function () {
    $appointment = appointmentWithIntent(null);

    $service = $this->partialMock(BookingPaymentService::class, function ($mock) {
        $mock->shouldNotReceive('retrieveIntent');
        $mock->shouldReceive('createIntent')
            ->once()
            ->andReturn(fakeBookingIntent('pi_premier', 'requires_payment_method', 7000));
    });

    $service->createPaymentIntent($appointment);

    expect($appointment->fresh()->stripe_payment_intent_id)->toBe('pi_premier');
});
