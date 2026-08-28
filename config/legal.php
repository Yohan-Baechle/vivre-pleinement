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
        'vat' => 'TVA non applicable, article 293 B du CGI',
        'phone' => env('LEGAL_PHONE'),
        'publication_director' => 'Laura Baechlé',
    ],

    'host' => [
        'name' => 'OVH SAS',
        'address' => '2 rue Kellermann, 59100 Roubaix, France',
        'website' => 'https://www.ovhcloud.com',
        'phone' => '1007',
    ],

    'webmaster' => [
        'name' => 'Byohan',
        'email' => 'contact@byohan.fr',
    ],

    'data_controller' => [
        'name' => 'Laura Baechlé',
        'address' => '7 Rue du Moulin Saintin',
    ],

    'mediator' => [
        'name' => 'CM2C — Centre de la Médiation de la Consommation de Conciliateurs de Justice',
        'address' => '49 rue de Ponthieu, 75008 Paris',
        'website' => 'https://www.cm2c.net',
    ],

    'cnil' => [
        'website' => 'https://www.cnil.fr',
        'complaint_url' => 'https://www.cnil.fr/fr/plaintes',
    ],

    'last_updated' => '2026-05-25',

];
