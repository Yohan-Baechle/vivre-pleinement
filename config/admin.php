<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compte admin Filament initial
    |--------------------------------------------------------------------------
    |
    | Utilisé par AdminUserSeeder. Passer par config() plutôt que env() directement
    | dans le seeder : avec `config:cache` (standard en production), env() renvoie
    | null pour toute clé non exposée par un fichier config.
    |
    | `password` n'a volontairement aucune valeur par défaut : le seeder utilise
    | updateOrCreate, donc un défaut ferait retomber le compte admin sur un mot de
    | passe connu à chaque `db:seed`. Sans ADMIN_PASSWORD, le seeder échoue.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@vivre-pleinement.local'),

    'name' => env('ADMIN_NAME', 'Laura B.'),

    'password' => env('ADMIN_PASSWORD'),

];
