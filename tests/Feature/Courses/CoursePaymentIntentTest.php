<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\PaymentIntent;

uses(RefreshDatabase::class);

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

    $service = $this->partialMock(CoursePaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->with('pi_existing')
            ->andReturn(fakeIntent('pi_existing', 'requires_payment_method', 14900));
        $mock->shouldNotReceive('createIntent');
    });

    $intent = $service->getOrCreatePaymentIntent($enrollment);

    expect($intent->id)->toBe('pi_existing');
});

it('réaligne le montant de l\'intent existant lorsque le prix a changé', function () {
    $enrollment = enrollmentWithIntent('pi_existing', priceCents: 19900);

    $service = $this->partialMock(CoursePaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->andReturn(fakeIntent('pi_existing', 'requires_payment_method', 14900));
        $mock->shouldReceive('updateIntentAmount')
            ->once()
            ->with('pi_existing', 19900)
            ->andReturn(fakeIntent('pi_existing', 'requires_payment_method', 19900));
        $mock->shouldNotReceive('createIntent');
    });

    $intent = $service->getOrCreatePaymentIntent($enrollment);

    expect($intent->amount)->toBe(19900);
});

it('crée un nouvel intent lorsque l\'ancien est déjà finalisé', function () {
    $enrollment = enrollmentWithIntent('pi_finalise');

    $service = $this->partialMock(CoursePaymentService::class, function ($mock) {
        $mock->shouldReceive('retrieveIntent')
            ->once()
            ->andReturn(fakeIntent('pi_finalise', 'succeeded', 14900));
        $mock->shouldReceive('resolveStripeCustomerId')->andReturn('cus_test');
        $mock->shouldReceive('createIntent')
            ->once()
            ->andReturn(fakeIntent('pi_nouveau', 'requires_payment_method', 14900));
    });

    $intent = $service->getOrCreatePaymentIntent($enrollment);

    expect($intent->id)->toBe('pi_nouveau')
        ->and($enrollment->fresh()->stripe_payment_intent_id)->toBe('pi_nouveau');
});

it('crée un intent et le mémorise sur l\'inscription au premier passage', function () {
    $enrollment = enrollmentWithIntent(null);

    $service = $this->partialMock(CoursePaymentService::class, function ($mock) {
        $mock->shouldNotReceive('retrieveIntent');
        $mock->shouldReceive('resolveStripeCustomerId')->andReturn('cus_test');
        $mock->shouldReceive('createIntent')
            ->once()
            ->andReturn(fakeIntent('pi_premier', 'requires_payment_method', 14900));
    });

    $service->getOrCreatePaymentIntent($enrollment);

    expect($enrollment->fresh()->stripe_payment_intent_id)->toBe('pi_premier');
});
