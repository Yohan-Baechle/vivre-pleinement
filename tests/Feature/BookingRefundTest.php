<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Filament\Admin\Resources\Appointments\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\User;
use App\Services\BookingPaymentService;
use App\Services\StripePaymentIntents;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

function paidAppointment(string $intentId = 'pi_appointment_refund'): Appointment
{
    return Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'price_cents' => 5000,
        'stripe_payment_intent_id' => $intentId,
    ]);
}

function appointmentRefundWebhook(?string $intentId = 'pi_appointment_refund'): void
{
    event(new WebhookReceived([
        'type' => 'charge.refunded',
        'data' => ['object' => [
            'id' => 'ch_appointment_refund',
            'payment_intent' => $intentId,
        ]],
    ]));
}

it('marque le paiement remboursé sur un webhook charge.refunded', function () {
    $appointment = paidAppointment();

    appointmentRefundWebhook();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Refunded);
});

it('laisse le rendez-vous au planning après un remboursement', function () {
    $appointment = paidAppointment();

    appointmentRefundWebhook();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('ignore un remboursement dont le PaymentIntent est inconnu', function () {
    $appointment = paidAppointment();

    appointmentRefundWebhook('pi_autre_paiement');

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('ne touche pas à un rendez-vous jamais payé', function () {
    $appointment = Appointment::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'stripe_payment_intent_id' => 'pi_appointment_refund',
    ]);

    appointmentRefundWebhook();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('est idempotent sur un rendez-vous déjà remboursé', function () {
    $appointment = paidAppointment();

    appointmentRefundWebhook();
    appointmentRefundWebhook();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Refunded);
});

it('permet à un admin de rembourser un rendez-vous', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $appointment = paidAppointment();

    $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('refundQuietly')
            ->once()
            ->with('pi_appointment_refund')
            ->andReturnTrue();
    });

    Livewire::test(ListAppointments::class)
        ->callAction(TestAction::make('refund')->table($appointment))
        ->assertHasNoActionErrors();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Refunded);
});

/**
 * Le statut ne doit jamais devancer Stripe : un rendez-vous affiché
 * « remboursé » sans crédit réel ne se découvre qu'à la réclamation du client.
 */
it('laisse le paiement intact quand Stripe refuse le remboursement', function () {
    $appointment = paidAppointment();

    $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('refundQuietly')->once()->andReturnFalse();
    });

    expect(app(BookingPaymentService::class)->issueRefund($appointment))->toBeFalse()
        ->and($appointment->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('ne rembourse rien quand aucun paiement Stripe n\'est rattaché', function () {
    $appointment = Appointment::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'stripe_payment_intent_id' => null,
    ]);

    $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldNotReceive('refundQuietly');
    });

    expect(app(BookingPaymentService::class)->issueRefund($appointment))->toBeFalse()
        ->and($appointment->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('ne rembourse pas deux fois un rendez-vous déjà remboursé', function () {
    $appointment = paidAppointment();
    $appointment->update(['payment_status' => PaymentStatus::Refunded]);

    $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldNotReceive('refundQuietly');
    });

    expect(app(BookingPaymentService::class)->issueRefund($appointment))->toBeFalse();
});
