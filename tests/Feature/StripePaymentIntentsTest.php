<?php

use App\Services\StripePaymentIntents;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

function stripeIntent(string $id, string $status, int $amount): PaymentIntent
{
    return PaymentIntent::constructFrom([
        'id' => $id,
        'status' => $status,
        'amount' => $amount,
        'client_secret' => $id.'_secret',
    ]);
}

it('ne cherche rien quand aucun intent n\'est encore rattaché', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldNotReceive('retrieve');
    });

    expect($intents->reusable(null, 14900))->toBeNull();
});

it('réutilise un intent encore ouvert dont le montant est bon', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('retrieve')
            ->once()
            ->with('pi_existing')
            ->andReturn(stripeIntent('pi_existing', 'requires_payment_method', 14900));
        $mock->shouldNotReceive('updateAmount');
    });

    expect($intents->reusable('pi_existing', 14900)->id)->toBe('pi_existing');
});

it('réaligne le montant lorsque le prix a changé entre-temps', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('retrieve')
            ->once()
            ->andReturn(stripeIntent('pi_existing', 'requires_payment_method', 14900));
        $mock->shouldReceive('updateAmount')
            ->once()
            ->with('pi_existing', 19900)
            ->andReturn(stripeIntent('pi_existing', 'requires_payment_method', 19900));
    });

    expect($intents->reusable('pi_existing', 19900)->amount)->toBe(19900);
});

it('refuse de réutiliser un intent déjà finalisé', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('retrieve')
            ->once()
            ->andReturn(stripeIntent('pi_finalise', 'succeeded', 14900));
    });

    expect($intents->reusable('pi_finalise', 14900))->toBeNull();
});

it('refuse de réutiliser un intent que Stripe ne retrouve pas', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('retrieve')->once()->andReturn(null);
    });

    expect($intents->reusable('pi_disparu', 14900))->toBeNull();
});

it('signale un remboursement impossible sans propager l\'échec', function () {
    Log::spy();

    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('refund')->once()->andThrow(new RuntimeException('Stripe indisponible'));
    });

    expect($intents->refundQuietly('pi_x'))->toBeFalse();

    Log::shouldHaveReceived('error')
        ->with('Remboursement Stripe impossible : à traiter dans le dashboard.', Mockery::any())
        ->once();
});

it('confirme un remboursement réussi', function () {
    $intents = $this->partialMock(StripePaymentIntents::class, function ($mock) {
        $mock->shouldReceive('refund')->once()->with('pi_x');
    });

    expect($intents->refundQuietly('pi_x'))->toBeTrue();
});
