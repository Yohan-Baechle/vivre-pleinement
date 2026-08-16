<?php

use App\Enums\BookOrderStatus;
use App\Mail\BookOrderConfirmation;
use App\Mail\BookOrderNotification;
use App\Models\BookOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

uses(LazilyRefreshDatabase::class);

function bookPaymentWebhook(BookOrder $order, string $intentId = 'pi_book_test', int $amount = 3700): void
{
    event(new WebhookReceived([
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => $intentId,
            'amount_received' => $amount,
            'currency' => 'eur',
            'metadata' => ['book_order_id' => (string) $order->id],
        ]],
    ]));
}

function bookRefundWebhook(string $intentId = 'pi_book_test'): void
{
    event(new WebhookReceived([
        'type' => 'charge.refunded',
        'data' => ['object' => [
            'id' => 'ch_book_test',
            'payment_intent' => $intentId,
        ]],
    ]));
}

it('marque la commande payée sur payment_intent.succeeded', function () {
    Mail::fake();
    $order = BookOrder::factory()->create();

    bookPaymentWebhook($order);

    $order->refresh();

    expect($order->status)->toBe(BookOrderStatus::Paid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_book_test')
        ->and($order->paid_at)->not->toBeNull();
});

it('envoie le lien au client et la notification à l\'admin', function () {
    Mail::fake();
    $order = BookOrder::factory()->create();

    bookPaymentWebhook($order);

    Mail::assertQueued(BookOrderConfirmation::class);
    Mail::assertQueued(BookOrderNotification::class);
});

it('enregistre le montant réellement encaissé, pas le prix courant', function () {
    Mail::fake();
    $order = BookOrder::factory()->create(['amount_cents' => 3700]);

    bookPaymentWebhook($order, amount: 2900);

    expect($order->fresh()->amount_cents)->toBe(2900);
});

it('ignore un webhook dupliqué sans renvoyer les emails', function () {
    Mail::fake();
    $order = BookOrder::factory()->create();

    bookPaymentWebhook($order);
    bookPaymentWebhook($order);

    Mail::assertQueuedCount(2);
});

it('ignore un webhook dont la commande est inconnue', function () {
    Mail::fake();
    $order = BookOrder::factory()->create();

    event(new WebhookReceived([
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => 'pi_autre',
            'metadata' => ['book_order_id' => '999999'],
        ]],
    ]));

    expect($order->fresh()->status)->toBe(BookOrderStatus::Pending);
});

it('révoque le téléchargement sur charge.refunded', function () {
    $order = BookOrder::factory()->paid()->create(['stripe_payment_intent_id' => 'pi_book_test']);

    bookRefundWebhook();

    $order->refresh();

    expect($order->status)->toBe(BookOrderStatus::Refunded)
        ->and($order->refunded_at)->not->toBeNull();
});

it('ne touche pas à une commande jamais payée lors d\'un remboursement', function () {
    $order = BookOrder::factory()->create(['stripe_payment_intent_id' => 'pi_book_test']);

    bookRefundWebhook();

    expect($order->fresh()->status)->toBe(BookOrderStatus::Pending);
});

it('ignore un remboursement dont le PaymentIntent est inconnu', function () {
    $order = BookOrder::factory()->paid()->create(['stripe_payment_intent_id' => 'pi_book_test']);

    bookRefundWebhook('pi_inconnu');

    expect($order->fresh()->status)->toBe(BookOrderStatus::Paid);
});
