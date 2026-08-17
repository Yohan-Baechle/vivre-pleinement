<?php

use App\Enums\AppointmentStatus;
use App\Enums\BookOrderStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\BookOrder;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\BookPaymentService;
use App\Services\StripePaymentIntents;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

/**
 * PaymentIntent minimal, tel que le renverrait l'API.
 */
function reconcilableIntent(string $id, string $status = 'succeeded', ?int $amount = 5000): PaymentIntent
{
    return PaymentIntent::constructFrom([
        'id' => $id,
        'status' => $status,
        'amount_received' => $amount,
        'currency' => 'eur',
    ]);
}

function mockReconcilableIntents(array $byId): void
{
    test()->mock(StripePaymentIntents::class, function ($mock) use ($byId) {
        $mock->shouldReceive('retrieve')
            ->andReturnUsing(fn (string $id) => $byId[$id] ?? null);
    });
}

/**
 * Ancienneté par défaut : la commande ignore ce qui date de moins de 15 min.
 */
function staleUnpaidAppointment(string $intentId = 'pi_rdv'): Appointment
{
    return Appointment::factory()->create([
        'status' => AppointmentStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'price_cents' => 5000,
        'stripe_payment_intent_id' => $intentId,
        'created_at' => CarbonImmutable::now()->subHour(),
    ]);
}

it('finalise un rendez-vous payé dont le webhook s\'est perdu', function () {
    $appointment = staleUnpaidAppointment();
    mockReconcilableIntents(['pi_rdv' => reconcilableIntent('pi_rdv')]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Paiements rattrapés : 1.')
        ->assertSuccessful();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('finalise une inscription payée et enregistre le montant encaissé', function () {
    $enrollment = Enrollment::factory()->pending()->create([
        'student_id' => Student::factory()->create()->id,
        'course_id' => Course::factory()->create()->id,
        'stripe_payment_intent_id' => 'pi_cours',
        'created_at' => CarbonImmutable::now()->subHour(),
    ]);

    mockReconcilableIntents(['pi_cours' => reconcilableIntent('pi_cours', amount: 12900)]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    $enrollment->refresh();

    expect($enrollment->status)->toBe(EnrollmentStatus::Active)
        ->and($enrollment->amount_paid_cents)->toBe(12900);
});

it('finalise une commande livre payée', function () {
    $order = BookOrder::factory()->create([
        'stripe_payment_intent_id' => 'pi_livre',
        'created_at' => CarbonImmutable::now()->subHour(),
    ]);

    mockReconcilableIntents(['pi_livre' => reconcilableIntent('pi_livre', amount: 3700)]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($order->fresh()->status)->toBe(BookOrderStatus::Paid);
});

it('ne touche pas à un paiement que Stripe n\'a pas confirmé', function () {
    $appointment = staleUnpaidAppointment();
    mockReconcilableIntents(['pi_rdv' => reconcilableIntent('pi_rdv', status: 'requires_payment_method')]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Aucun paiement en souffrance.')
        ->assertSuccessful();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('ne touche à rien quand Stripe ne connaît pas l\'intent', function () {
    $appointment = staleUnpaidAppointment();
    mockReconcilableIntents([]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('laisse au webhook le temps d\'arriver sur un paiement récent', function () {
    $appointment = Appointment::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'stripe_payment_intent_id' => 'pi_rdv',
        'created_at' => CarbonImmutable::now()->subMinutes(2),
    ]);

    mockReconcilableIntents(['pi_rdv' => reconcilableIntent('pi_rdv')]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Aucun paiement en souffrance.')
        ->assertSuccessful();

    expect($appointment->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('respecte le délai passé en option', function () {
    Appointment::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'price_cents' => 5000,
        'stripe_payment_intent_id' => 'pi_rdv',
        'created_at' => CarbonImmutable::now()->subMinutes(3),
    ]);

    mockReconcilableIntents(['pi_rdv' => reconcilableIntent('pi_rdv')]);

    $this->artisan('payments:reconcile', ['--minutes' => 1])
        ->expectsOutputToContain('Paiements rattrapés : 1.')
        ->assertSuccessful();
});

it('ignore les enregistrements sans PaymentIntent', function () {
    Appointment::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'stripe_payment_intent_id' => null,
        'created_at' => CarbonImmutable::now()->subHour(),
    ]);

    mockReconcilableIntents([]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Aucun paiement en souffrance.')
        ->assertSuccessful();
});

it('est idempotente, un second passage ne rejoue rien', function () {
    $order = BookOrder::factory()->create([
        'stripe_payment_intent_id' => 'pi_livre',
        'created_at' => CarbonImmutable::now()->subHour(),
    ]);

    mockReconcilableIntents(['pi_livre' => reconcilableIntent('pi_livre', amount: 3700)]);

    $this->artisan('payments:reconcile')->assertSuccessful();
    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Aucun paiement en souffrance.')
        ->assertSuccessful();

    expect($order->fresh()->status)->toBe(BookOrderStatus::Paid);
});

it('poursuit les autres lignes quand une finalisation échoue', function () {
    BookOrder::factory()->count(2)->sequence(
        ['stripe_payment_intent_id' => 'pi_casse'],
        ['stripe_payment_intent_id' => 'pi_ok'],
    )->create(['created_at' => CarbonImmutable::now()->subHour()]);

    mockReconcilableIntents([
        'pi_casse' => reconcilableIntent('pi_casse', amount: 3700),
        'pi_ok' => reconcilableIntent('pi_ok', amount: 3700),
    ]);

    /**
     * La première finalisation lève, la seconde doit malgré tout être tentée :
     * une commande planifiée ne doit pas s'arrêter au premier incident.
     */
    $this->mock(BookPaymentService::class, function ($mock) {
        $mock->shouldReceive('fulfill')
            ->twice()
            ->andReturnUsing(
                fn () => throw new RuntimeException('Stripe indisponible'),
                fn () => null,
            );
    });

    Log::spy();

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('Paiements rattrapés : 1.')
        ->assertSuccessful();

    Log::shouldHaveReceived('error')
        ->withArgs(fn (string $message) => str_contains($message, 'Rattrapage impossible'));
});
