<?php

use App\Listeners\HandleStripeChargeRefunded;
use App\Listeners\HandleStripePaymentSucceeded;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

it('retries payment_intent.succeeded processing several times with a backoff before giving up', function () {
    $listener = app(HandleStripePaymentSucceeded::class);

    expect($listener->tries)->toBeGreaterThan(1)
        ->and($listener->backoff)->toBeArray()
        ->and($listener->backoff)->not->toBeEmpty();
});

it('logs critically instead of failing silently when payment_intent.succeeded processing is exhausted', function () {
    Log::spy();

    $listener = app(HandleStripePaymentSucceeded::class);
    $event = new WebhookReceived(['type' => 'payment_intent.succeeded', 'data' => ['object' => []]]);

    $listener->failed($event, new RuntimeException('Stripe indisponible'));

    Log::shouldHaveReceived('critical')->once();
});

it('retries charge.refunded processing several times with a backoff before giving up', function () {
    $listener = app(HandleStripeChargeRefunded::class);

    expect($listener->tries)->toBeGreaterThan(1)
        ->and($listener->backoff)->toBeArray()
        ->and($listener->backoff)->not->toBeEmpty();
});

it('logs critically instead of failing silently when charge.refunded processing is exhausted', function () {
    Log::spy();

    $listener = app(HandleStripeChargeRefunded::class);
    $event = new WebhookReceived(['type' => 'charge.refunded', 'data' => ['object' => []]]);

    $listener->failed($event, new RuntimeException('Stripe indisponible'));

    Log::shouldHaveReceived('critical')->once();
});
