<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNow
{
    /**
     * Notifie IndexNow (Bing, Yandex, etc.) qu'une ou plusieurs URLs ont changé.
     * No-op silencieux quand aucune clé n'est configurée (local, CI).
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

        try {
            Http::timeout(5)->post('https://api.indexnow.org/indexnow', [
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
