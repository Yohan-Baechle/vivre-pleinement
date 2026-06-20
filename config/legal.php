<?php

/*
|--------------------------------------------------------------------------
| Informations légales du site
|--------------------------------------------------------------------------
|
| Sources d'autorité : LCEN art. 6 (mentions obligatoires éditeur/hébergeur),
| RGPD art. 13-14 (informations à fournir aux personnes concernées),
| recommandations CNIL sur les cookies (délibération 2020-091).
|
*/

return [

    'site' => [
        'name' => 'Vivre Pleinement',
        'domain' => env('LEGAL_SITE_DOMAIN', 'vivre-pleinement.fr'),
        'url' => env('APP_URL', 'https://vivre-pleinement.fr'),
        'tagline' => 'Accompagnement spécialisé dans les troubles anxieux',
    ],

    'editor' => [
        'type' => 'Auto-entrepreneur',
        'name' => 'Laura Baechlé',
        'address' => '7 Rue du Moulin Saintin',
        'siret' => '90377213500013',
        'email' => 'jasiewicz.laura@gmail.com',
        'phone' => env('LEGAL_PHONE'),
        'publication_director' => 'Laura Baechlé',
    ],

    'host' => [
        'name' => 'Hetzner Online GmbH',
        'address' => 'Industriestr. 25, 91710 Gunzenhausen, Allemagne',
        'website' => 'https://www.hetzner.com',
        'phone' => '+49 9831 505-0',
    ],

    'webmaster' => [
        'name' => 'Evogenis',
        'email' => 'contact@evogenis.com',
    ],

    'data_controller' => [
        'name' => 'Laura Baechlé',
        'email' => 'jasiewicz.laura@gmail.com',
        'address' => '7 Rue du Moulin Saintin',
    ],

    'mediator' => [
        'name' => 'CNPM Médiation Consommation',
        'address' => '27 Avenue de la Libération, 42400 Saint-Chamond',
        'website' => 'https://cnpm-mediation-consommation.eu',
    ],

    'cnil' => [
        'website' => 'https://www.cnil.fr',
        'complaint_url' => 'https://www.cnil.fr/fr/plaintes',
    ],

    'last_updated' => '2026-05-25',

];
