<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Livewire\BookingCalendar;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentSlotUnavailable;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Services\BookingPaymentService;
use App\Services\StripePaymentIntents;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Livewire\Livewire;
use Stripe\Exception\ApiConnectionException;
use Stripe\PaymentIntent;

uses(LazilyRefreshDatabase::class);

function payableService(int $priceCents = 7000): AppointmentService
{
    $service = AppointmentService::factory()->create([
        'duration_minutes' => 60,
        'price_cents' => $priceCents,
        'min_notice_hours' => 12,
    ]);
    foreach (range(0, 6) as $dow) {
        Availability::factory()->dayOfWeek($dow)->create();
    }

    return $service;
}

it('creates an unpaid pending appointment and redirects to the on-site payment page', function () {
    Mail::fake();
    $service = payableService();
    $slot = CarbonImmutable::now()->addDays(3)->setTime(10, 0);

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertRedirect();

    $appointment = Appointment::query()->firstOrFail();
    expect($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and($appointment->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($appointment->token)->not->toBeNull();

    Mail::assertNothingQueued();
});

it('renders the payment page with a client secret for a payable appointment', function () {
    $service = payableService();
    $start = CarbonImmutable::now()->addDays(3)->setTime(10, 0);
    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'price_cents' => 7000,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
    ]);

    $intent = PaymentIntent::constructFrom(['id' => 'pi_test', 'client_secret' => 'pi_test_secret_123']);
    $this->mock(BookingPaymentService::class)
        ->shouldReceive('createPaymentIntent')
        ->once()
        ->andReturn($intent);

    $this->get(route('booking.pay', $appointment->token))
        ->assertOk()
        ->assertSee('pi_test_secret_123', escape: false)
        ->assertSee('payment-element', escape: false);
});

it('returns a 503 instead of a raw 500 when Stripe is unavailable', function () {
    $service = payableService();
    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'price_cents' => 7000,
    ]);

    $this->mock(BookingPaymentService::class)
        ->shouldReceive('createPaymentIntent')
        ->once()
        ->andThrow(new ApiConnectionException('Stripe est indisponible'));

    $this->get(route('booking.pay', $appointment->token))
        ->assertStatus(503);
});

it('redirects the payment page to confirmation if already paid', function () {
    $service = payableService();
    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'payment_status' => PaymentStatus::Paid,
        'status' => AppointmentStatus::Confirmed,
    ]);

    $this->get(route('booking.pay', $appointment->token))
        ->assertRedirect(route('booking.confirmation', $appointment->token));
});

it('keeps the direct flow for a free service', function () {
    Mail::fake();
    $service = payableService(0);
    $slot = CarbonImmutable::now()->addDays(3)->setTime(10, 0);

    Livewire::test(BookingCalendar::class, ['service' => $service])
        ->set('selectedSlot', $slot->toIso8601String())
        ->set('firstName', 'Camille')
        ->set('email', 'camille@gmail.com')
        ->set('consent', true)
        ->call('book')
        ->assertRedirect();

    $appointment = Appointment::query()->firstOrFail();
    expect($appointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->payment_status)->toBe(PaymentStatus::NotRequired);

    Mail::assertQueued(AppointmentConfirmation::class);
});

it('fulfills the appointment on a payment_intent.succeeded webhook', function () {
    Mail::fake();
    $service = payableService();
    $start = CarbonImmutable::now()->addDays(3)->setTime(10, 0);
    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
    ]);

    event(new WebhookReceived([
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => 'pi_test_123',
            'metadata' => ['appointment_id' => $appointment->id],
        ]],
    ]));

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);

    Mail::assertQueued(AppointmentConfirmation::class);
    Mail::assertQueued(AppointmentNotification::class);
});

it('ignores a webhook of a different type', function () {
    Mail::fake();
    $appointment = Appointment::factory()->create(['payment_status' => PaymentStatus::Unpaid]);

    event(new WebhookReceived([
        'type' => 'payment_intent.created',
        'data' => ['object' => ['id' => 'pi_x', 'metadata' => ['appointment_id' => $appointment->id]]],
    ]));

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
    Mail::assertNothingQueued();
});

it('refunds and apologises if the slot was taken during payment', function () {
    Mail::fake();
    $service = payableService();
    $start = CarbonImmutable::now()->addDays(3)->setTime(10, 0);

    $appointment = Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
    ]);

    Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'status' => AppointmentStatus::Confirmed,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
    ]);

    app(BookingPaymentService::class)->fulfill($appointment, null);

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
    Mail::assertQueued(AppointmentSlotUnavailable::class);
    Mail::assertNotQueued(AppointmentConfirmation::class);
});

it('is idempotent on duplicate paid webhooks', function () {
    Mail::fake();
    $appointment = Appointment::factory()->create(['payment_status' => PaymentStatus::Paid, 'status' => AppointmentStatus::Confirmed]);

    app(BookingPaymentService::class)->fulfill($appointment);

    Mail::assertNothingQueued();
});

it('rembourse automatiquement un second paiement arrivé sur un rendez-vous déjà payé', function () {
    Mail::fake();
    $appointment = Appointment::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => AppointmentStatus::Confirmed,
        'stripe_payment_intent_id' => 'pi_premier',
    ]);

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('refundQuietly')->once()->with('pi_second')->andReturnTrue();
    });

    app(BookingPaymentService::class)->fulfill($appointment, 'pi_second');

    expect($appointment->fresh()->stripe_payment_intent_id)->toBe('pi_premier');
    Mail::assertNothingQueued();
});
