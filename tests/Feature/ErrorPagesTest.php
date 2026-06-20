<?php

use function Pest\Laravel\get;

it('rend une page 404 personnalisée pour une URL inconnue', function () {
    $response = get('/cette-page-nexiste-pas-'.uniqid());

    $response->assertNotFound();
    $response->assertSee('Cette page est introuvable');
    $response->assertSee('Retour à l\'accueil', false);
});

it('rend les vues d\'erreur courantes avec le layout partagé', function (string $view, string $expected) {
    $html = view("errors.{$view}")->render();

    expect($html)->toContain($expected);
})->with([
    ['403', 'Accès non autorisé'],
    ['404', 'Cette page est introuvable'],
    ['405', 'Action non autorisée'],
    ['419', 'Votre session a expiré'],
    ['429', 'Trop de requêtes'],
    ['500', 'Une erreur est survenue'],
    ['503', 'Site en maintenance'],
]);
