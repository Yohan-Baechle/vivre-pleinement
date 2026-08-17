<?php

use App\Enums\BookOrderStatus;
use App\Models\BookOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

/**
 * Disque simulé : sans cela, chaque exécution laisse un faux PDF dans
 * storage/app/private, que le rollback de la base ne nettoie pas.
 */
beforeEach(fn () => Storage::fake('local'));

beforeEach(function () {
    $this->solo = Product::factory()->create([
        'slug' => 'livre',
        'name' => 'Le livre seul',
        'price_cents' => 3700,
    ]);

    $this->coaching = Product::factory()->create([
        'slug' => 'livre-coaching',
        'name' => 'Le livre + 1h de coaching',
        'price_cents' => 7000,
    ]);

    /**
     * Sans fichier livrable, aucune offre n'est achetable : la disponibilité
     * a ses propres tests, ceux-ci portent sur le tunnel lui-même.
     */
    $this->solo->addMedia(UploadedFile::fake()->create('livre.pdf', 12))
        ->toMediaCollection('download');
});

it('renders the book landing page', function () {
    $this->get(route('book.show'))->assertOk();
});

it('affiche les prix du catalogue plutôt que des montants figés', function () {
    $this->solo->update(['price_cents' => 4200]);

    $this->get(route('book.show'))
        ->assertOk()
        ->assertSee('42', false)
        ->assertDontSee('Obtenir le livre · 37', false);
});

it('affiche le formulaire de commande pour chaque formule', function (string $offer, string $expected) {
    $this->get(route('book.checkout', $offer))
        ->assertOk()
        ->assertSee($expected)
        ->assertSee('Continuer vers le paiement');
})->with([
    ['livre', 'Le livre seul'],
    ['livre-coaching', 'Le livre + 1h de coaching'],
]);

it('404s for an unknown offer', function () {
    $this->get('/livre/commande/unknown-offer')->assertNotFound();
});

it('404 quand le produit est désactivé', function () {
    $this->solo->update(['is_active' => false]);

    $this->get(route('book.checkout', 'livre'))->assertNotFound();
});

it('crée une commande en attente et redirige vers le paiement', function () {
    $response = $this->post(route('book.start', 'livre'), [
        'first_name' => 'Camille',
        'last_name' => 'Durand',
        'email' => 'camille@gmail.com',
        'consent' => '1',
    ]);

    $order = BookOrder::query()->firstOrFail();

    expect($order->amount_cents)->toBe(3700)
        ->and($order->status)->toBe(BookOrderStatus::Pending)
        ->and($order->customer_email)->toBe('camille@gmail.com');

    $response->assertRedirect(route('book.pay', $order->token));
});

it('fige le montant sur le produit et ignore un prix soumis', function () {
    $this->post(route('book.start', 'livre'), [
        'first_name' => 'Camille',
        'last_name' => 'Durand',
        'email' => 'camille@gmail.com',
        'consent' => '1',
        'amount_cents' => 1,
    ]);

    expect(BookOrder::query()->firstOrFail()->amount_cents)->toBe(3700);
});

it('refuse une commande sans consentement', function () {
    $this->post(route('book.start', 'livre'), [
        'first_name' => 'Camille',
        'last_name' => 'Durand',
        'email' => 'camille@gmail.com',
    ])->assertSessionHasErrors('consent');

    expect(BookOrder::query()->count())->toBe(0);
});

it('rejette une soumission piégée par le champ honeypot', function () {
    $this->post(route('book.start', 'livre'), [
        'first_name' => 'Robot',
        'last_name' => 'Spam',
        'email' => 'robot@gmail.com',
        'consent' => '1',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(BookOrder::query()->count())->toBe(0);
});
