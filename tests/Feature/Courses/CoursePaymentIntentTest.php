<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use App\Services\StripePaymentIntents;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Stripe\PaymentIntent;

uses(LazilyRefreshDatabase::class);

function enrollmentWithIntent(?string $intentId, int $priceCents = 14900): Enrollment
{
    $course = Course::factory()->create(['price_cents' => $priceCents]);

    return Enrollment::factory()->pending()->create([
        'course_id' => $course->id,
        'stripe_payment_intent_id' => $intentId,
    ]);
}

function fakeIntent(string $id, string $status, int $amount): PaymentIntent
{
    return PaymentIntent::constructFrom([
        'id' => $id,
        'status' => $status,
        'amount' => $amount,
        'client_secret' => $id.'_secret',
    ]);
}

it('réutilise le PaymentIntent déjà créé au lieu d\'en générer un second', function () {
    $enrollment = enrollmentWithIntent('pi_existing');

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')
            ->once()
            ->with('pi_existing', 14900)
            ->andReturn(fakeIntent('pi_existing', 'requires_payment_method', 14900));
        $mock->shouldNotReceive('create');
    });

    $intent = app(CoursePaymentService::class)->getOrCreatePaymentIntent($enrollment);

    expect($intent->id)->toBe('pi_existing');
});

it('crée un nouvel intent lorsqu\'aucun n\'est réutilisable', function () {
    $enrollment = enrollmentWithIntent('pi_finalise');

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')->once()->andReturn(null);
        $mock->shouldReceive('create')
            ->once()
            ->andReturn(fakeIntent('pi_nouveau', 'requires_payment_method', 14900));
    });

    $service = Mockery::mock(CoursePaymentService::class, [app(StripePaymentIntents::class)])->makePartial();
    $service->shouldReceive('resolveStripeCustomerId')->andReturn('cus_test');

    $intent = $service->getOrCreatePaymentIntent($enrollment);

    expect($intent->id)->toBe('pi_nouveau')
        ->and($enrollment->fresh()->stripe_payment_intent_id)->toBe('pi_nouveau');
});

it('crée un intent et le mémorise sur l\'inscription au premier passage', function () {
    $enrollment = enrollmentWithIntent(null);

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')->once()->with(null, 14900)->andReturn(null);
        $mock->shouldReceive('create')
            ->once()
            ->andReturn(fakeIntent('pi_premier', 'requires_payment_method', 14900));
    });

    $service = Mockery::mock(CoursePaymentService::class, [app(StripePaymentIntents::class)])->makePartial();
    $service->shouldReceive('resolveStripeCustomerId')->andReturn('cus_test');

    $service->getOrCreatePaymentIntent($enrollment);

    expect($enrollment->fresh()->stripe_payment_intent_id)->toBe('pi_premier');
});

it('transmet le prix courant de la formation au calcul de réutilisation', function () {
    $enrollment = enrollmentWithIntent('pi_existing', priceCents: 19900);

    $this->mock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('reusable')
            ->once()
            ->with('pi_existing', 19900)
            ->andReturn(fakeIntent('pi_existing', 'requires_payment_method', 19900));
    });

    expect(app(CoursePaymentService::class)->getOrCreatePaymentIntent($enrollment)->amount)->toBe(19900);
});
