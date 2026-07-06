<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compte admin Filament initial
    |--------------------------------------------------------------------------
    |
    | Utilisé par AdminUserSeeder. Passer par config() plutôt que env() directement
    | dans le seeder : avec `config:cache` (standard en production), env() renvoie
    | null pour toute clé non exposée par un fichier config, et le seeder retombait
    | silencieusement sur son mot de passe par défaut.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@vivre-pleinement.local'),

    'name' => env('ADMIN_NAME', 'Laura B.'),

    'password' => env('ADMIN_PASSWORD', 'password'),

];
