<?php

/*
|--------------------------------------------------------------------------
| Proxys de confiance
|--------------------------------------------------------------------------
|
| Le site est servi derrière Cloudflare (plan gratuit) puis un reverse proxy
| local sur le VPS OVH. Sans cette liste, `$request->ip()` renvoie l'adresse du
| proxy : toutes les limitations d'envoi par IP se confondent en un compteur
| global unique, `$request->secure()` est faux — donc HSTS n'est jamais émis —
| et l'IP enregistrée sur les commentaires perd toute valeur.
|
| Cette clé est lue par `Illuminate\Http\Middleware\TrustProxies` à chaque
| requête : elle ne peut pas être résolue depuis bootstrap/app.php, où la
| configuration n'est pas encore chargée.
|
| Les plages Cloudflare viennent de https://www.cloudflare.com/ips/ et bougent
| rarement ; TRUSTED_PROXIES les surcharge sans redéploiement (valeurs séparées
| par des virgules, ou `*` quand l'origine n'accepte déjà que le proxy).
|
| 127.0.0.1 et ::1 couvrent le reverse proxy local devant PHP-FPM.
|
*/

$cloudflare = [
    '127.0.0.1',
    '::1',

    '173.245.48.0/20',
    '103.21.244.0/22',
    '103.22.200.0/22',
    '103.31.4.0/22',
    '141.101.64.0/18',
    '108.162.192.0/18',
    '190.93.240.0/20',
    '188.114.96.0/20',
    '197.234.240.0/22',
    '198.41.128.0/17',
    '162.158.0.0/15',
    '104.16.0.0/13',
    '104.24.0.0/14',
    '172.64.0.0/13',
    '131.0.72.0/22',

    '2400:cb00::/32',
    '2606:4700::/32',
    '2803:f800::/32',
    '2405:b500::/32',
    '2405:8100::/32',
    '2a06:98c0::/29',
    '2c0f:f248::/32',
];

$configured = trim((string) env('TRUSTED_PROXIES'));

return [

    'proxies' => match (true) {
        $configured === '' => $cloudflare,
        $configured === '*' => '*',
        default => array_values(array_filter(
            array_map(trim(...), explode(',', $configured)),
            static fn (string $proxy): bool => $proxy !== '',
        )),
    },

];
