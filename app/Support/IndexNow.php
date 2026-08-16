<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Illuminate\Support\defer;

class IndexNow
{
    /**
     * Notifie IndexNow (Bing, Yandex, etc.) qu'une ou plusieurs URLs ont
     * changé. No-op silencieux quand aucune clé n'est configurée (local, CI).
     *
     * L'appel est différé après l'envoi de la réponse : il part des observers
     * de Post, Video et Course, donc de chaque enregistrement dans l'admin et
     * de chaque itération des commandes d'édition en masse. Un tiers lent ne
     * doit pas s'ajouter au temps de sauvegarde vu par l'administrateur.
     *
     * Le nom du callback dédoublonne les pings d'une même URL sur un même cycle
     * (un modèle sauvegardé deux fois de suite ne notifie qu'une fois).
     *
     * `defer` est importé depuis `Illuminate\Support` et non pris dans l'espace
     * global : l'extension Swoole déclare sa propre fonction `defer()`, ce qui
     * empêche Laravel d'enregistrer son helper global et détournerait l'appel.
     *
     * @param  list<string>|string  $urls
     */
    public static function ping(array|string $urls): void
    {
        $key = config('services.indexnow.key');

        if (blank($key)) {
            return;
        }

        $urls = array_values(array_unique((array) $urls));

        defer(
            fn () => self::send($key, $urls),
            'indexnow:'.implode(',', $urls),
        );
    }

    /**
     * @param  list<string>  $urls
     */
    private static function send(string $key, array $urls): void
    {
        try {
            Http::connectTimeout(3)->timeout(5)->post('https://api.indexnow.org/indexnow', [
                'host' => parse_url(config('app.url'), PHP_URL_HOST),
                'key' => $key,
                'keyLocation' => url("/{$key}.txt"),
                'urlList' => $urls,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('IndexNow ping failed', ['error' => $exception->getMessage()]);
        }
    }
}
