<?php

use App\Models\BookOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(LazilyRefreshDatabase::class);

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
});

function attachBookFile(Product $product): void
{
    $product->addMedia(UploadedFile::fake()->create('livre.pdf', 12))
        ->toMediaCollection('download');
}

it('considère une offre sans fichier comme non livrable', function () {
    expect($this->solo->isDeliverable())->toBeFalse();
});

it('rend la formule coaching livrable via le fichier du livre seul', function () {
    attachBookFile($this->solo);

    expect($this->coaching->fresh()->isDeliverable())->toBeTrue()
        ->and($this->coaching->fresh()->usesInheritedFile())->toBeTrue();
});

it('préfère le fichier propre du produit à celui hérité', function () {
    attachBookFile($this->solo);
    $this->coaching->addMedia(UploadedFile::fake()->create('coaching.pdf', 12))
        ->toMediaCollection('download');

    $coaching = $this->coaching->fresh();

    expect($coaching->deliverableMedia()->file_name)->toBe('coaching.pdf')
        ->and($coaching->usesInheritedFile())->toBeFalse();
});

it('propose d\'être prévenu au lieu d\'un lien d\'achat quand le fichier manque', function () {
    $this->get(route('book.show'))
        ->assertOk()
        ->assertSee('Être prévenu de la sortie')
        ->assertDontSee(route('book.checkout', 'livre'));
});

it('affiche les liens d\'achat dès que le fichier est en place', function () {
    attachBookFile($this->solo);

    $this->get(route('book.show'))
        ->assertOk()
        ->assertSee(route('book.checkout', 'livre'))
        ->assertSee(route('book.checkout', 'livre-coaching'))
        ->assertDontSee('Être prévenu de la sortie');
});

it('annonce OutOfStock dans les données structurées sans fichier', function () {
    $this->get(route('book.show'))
        ->assertOk()
        ->assertSee('schema.org/OutOfStock', false)
        ->assertDontSee('schema.org/InStock', false);
});

it('annonce InStock une fois le fichier disponible', function () {
    attachBookFile($this->solo);

    $this->get(route('book.show'))
        ->assertOk()
        ->assertSee('schema.org/InStock', false)
        ->assertDontSee('schema.org/OutOfStock', false);
});

it('refuse le formulaire de commande tant que le fichier manque', function (string $offer) {
    $this->get(route('book.checkout', $offer))->assertNotFound();
})->with(['livre', 'livre-coaching']);

it('refuse la création de commande tant que le fichier manque', function () {
    $this->post(route('book.start', 'livre'), [
        'first_name' => 'Camille',
        'last_name' => 'Durand',
        'email' => 'camille@gmail.com',
        'consent' => '1',
    ])->assertNotFound();

    expect(BookOrder::query()->count())->toBe(0);
});

it('ouvre le tunnel dès que le fichier est en place', function () {
    attachBookFile($this->solo);

    $this->get(route('book.checkout', 'livre'))->assertOk();
    $this->get(route('book.checkout', 'livre-coaching'))->assertOk();
});

it('détourne du paiement une commande dont le fichier a disparu', function () {
    attachBookFile($this->solo);

    $order = BookOrder::factory()->create(['product_id' => $this->solo->id]);

    $this->solo->clearMediaCollection('download');

    $this->get(route('book.pay', $order->token))
        ->assertRedirect(route('book.show'))
        ->assertSessionHas('status');
});

it('ne répète pas l\'invitation à être prévenu sur chaque carte', function () {
    $html = $this->get(route('book.show'))->assertOk()->getContent();

    /**
     * Une invitation par section, pas une par formule : quatre appels à
     * l'action identiques sur la page seraient un doublon d'intention.
     */
    expect(substr_count($html, 'Être prévenu de la sortie'))->toBe(2)
        ->and(substr_count($html, 'Bientôt disponible'))->toBe(2);
});

it('ne promet pas un paiement sécurisé quand rien n\'est en vente', function () {
    $this->get(route('book.show'))
        ->assertOk()
        ->assertDontSee('Paiement sécurisé par Stripe');
});

it('rend les vrais boutons d\'achat dès que le fichier est en place', function () {
    attachBookFile($this->solo);

    $html = $this->get(route('book.show'))->assertOk()->getContent();

    expect(substr_count($html, 'Être prévenu de la sortie'))->toBe(0)
        ->and(substr_count($html, 'Bientôt disponible'))->toBe(0)
        ->and($html)->toContain('Paiement sécurisé par Stripe');
});
