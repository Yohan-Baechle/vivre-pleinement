<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hôtes de confiance
    |--------------------------------------------------------------------------
    |
    | Laravel répond par défaut à n'importe quel en-tête Host et s'en sert pour
    | construire les URL absolues. Deux conséquences concrètes ici : le sitemap
    | est mis en cache une heure avec des URL dérivées du Host, et l'URL de
    | confirmation transmise à Brevo l'est aussi. Un Host falsifié empoisonne
    | donc le sitemap servi à Google et détourne les confirmations newsletter.
    |
    | Les valeurs sont des expressions régulières. L'hôte d'APP_URL et ses
    | sous-domaines sont acceptés d'office par le middleware, cette liste ne
    | sert donc qu'aux domaines supplémentaires.
    |
    | Les proxys de confiance vivent dans config/trustedproxy.php, à l'endroit
    | où le middleware du framework va les chercher.
    |
    */

    'trusted_hosts' => array_values(array_filter(
        array_map(trim(...), explode(',', (string) env('TRUSTED_HOSTS', ''))),
        static fn (string $host): bool => $host !== '',
    )),

];
