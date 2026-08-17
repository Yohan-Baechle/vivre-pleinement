<?php

use App\Models\BookOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

/**
 * Disque simulé : sans cela, chaque exécution laisse un faux PDF dans
 * storage/app/private, que le rollback de la base ne nettoie pas.
 */
beforeEach(fn () => Storage::fake('local'));

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

it('sert le fichier à une commande payée via un lien signé', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $this->get($order->downloadUrl())
        ->assertOk()
        ->assertDownload('livre.pdf');
});

it('refuse le téléchargement à une commande non payée', function () {
    $order = BookOrder::factory()->create();
    attachDownloadable($order);

    $this->get($order->downloadUrl())->assertForbidden();
});

it('coupe le téléchargement après un remboursement', function () {
    $order = BookOrder::factory()->refunded()->create();
    attachDownloadable($order);

    $this->get($order->downloadUrl())->assertForbidden();
});

it('répond 404 tant qu\'aucun fichier n\'est rattaché au produit', function () {
    $order = BookOrder::factory()->paid()->create();

    $this->get($order->downloadUrl())->assertNotFound();
});

it('renvoie vers la commande plutôt qu\'une erreur quand le lien a expiré', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $url = $order->downloadUrl();

    $this->travel(BookOrder::DOWNLOAD_LINK_DAYS + 1)->days();

    $this->get($url)
        ->assertRedirect(route('book.success', $order->token))
        ->assertSessionHas('status');
});

it('refuse un lien de téléchargement non signé', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $this->get(route('book.download', $order->token))
        ->assertRedirect(route('book.success', $order->token));
});

it('refuse un token inconnu', function () {
    $this->get('/livre/telecharger/'.str_repeat('a', 48))->assertNotFound();
});

it('affiche un lien de téléchargement frais sur la page de remerciement', function () {
    $order = BookOrder::factory()->paid()->create();
    attachDownloadable($order);

    $this->get(route('book.success', $order->token))
        ->assertOk()
        ->assertSee('signature=', false);
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
