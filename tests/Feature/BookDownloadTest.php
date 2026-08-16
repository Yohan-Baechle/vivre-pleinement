<?php

use App\Models\BookOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(LazilyRefreshDatabase::class);

/**
 * Attache un fichier vendu au produit de la commande, comme le ferait un
 * téléversement depuis la fiche produit en admin.
 */
function attachDownloadable(BookOrder $order): void
{
    $order->product
        ->addMedia(UploadedFile::fake()->create('livre.pdf', 12))
        ->toMediaCollection('download');
}

it('sert le fichier à une commande payée', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $this->get(route('book.download', $order->token))
        ->assertOk()
        ->assertDownload('livre.pdf');
});

it('refuse le téléchargement à une commande non payée', function () {
    $order = BookOrder::factory()->create();
    attachDownloadable($order);

    $this->get(route('book.download', $order->token))->assertForbidden();
});

it('coupe le téléchargement après un remboursement', function () {
    $order = BookOrder::factory()->refunded()->create();
    attachDownloadable($order);

    $this->get(route('book.download', $order->token))->assertForbidden();
});

it('répond 404 tant qu\'aucun fichier n\'est rattaché au produit', function () {
    $order = BookOrder::factory()->paid()->create();

    $this->get(route('book.download', $order->token))->assertNotFound();
});

it('refuse un token inconnu', function () {
    $this->get('/livre/telecharger/'.str_repeat('a', 48))->assertNotFound();
});

it('affiche le lien de téléchargement sur la page de remerciement', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $this->get(route('book.success', $order->token))
        ->assertOk()
        ->assertSee(route('book.download', $order->token));
});

it('fait patienter la page de remerciement tant que le webhook n\'a pas répondu', function () {
    $order = BookOrder::factory()->create();

    $this->get(route('book.success', $order->token))
        ->assertOk()
        ->assertSee('Paiement reçu')
        ->assertSee('http-equiv="refresh"', false);
});

it('renvoie une commande déjà payée de la page de paiement vers le remerciement', function () {
    $order = BookOrder::factory()->paid()->create();

    $this->get(route('book.pay', $order->token))
        ->assertRedirect(route('book.success', $order->token));
});
