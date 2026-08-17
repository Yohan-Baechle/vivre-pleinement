<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    private const CACHE_KEY = 'redirects.map';

    private const CACHE_MINUTES = 1440;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $path = '/'.ltrim($request->path(), '/');
        $redirect = $this->map()[$path] ?? null;

        if ($redirect === null) {
            return $response;
        }

        $target = $this->resolveTarget($redirect['to_path']);
        if ($target === null) {
            return $response;
        }

        $this->recordHit($redirect['id']);

        return redirect($target, $redirect['status_code']);
    }

    /**
     * Table des redirections indexée par chemin source, mise en cache.
     *
     * Sans elle, chaque 404 déclenchait une requête SQL : marteler des URL
     * inexistantes suffisait à charger la base. Le cache est invalidé par
     * RedirectObserver dès qu'une entrée change dans l'admin.
     *
     * @return array<string, array{id: int, to_path: string, status_code: int}>
     */
    private function map(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            fn (): array => Redirect::query()
                ->get(['id', 'from_path', 'to_path', 'status_code'])
                ->keyBy('from_path')
                ->map(fn (Redirect $redirect): array => [
                    'id' => $redirect->id,
                    'to_path' => $redirect->to_path,
                    'status_code' => $redirect->status_code,
                ])
                ->all(),
        );
    }

    /**
     * Incrémente le compteur par une mise à jour directe, sans passer par le
     * modèle : un événement Eloquent ici viderait le cache à chaque visite et
     * annulerait tout l'intérêt de la mise en cache.
     */
    private function recordHit(int $id): void
    {
        Redirect::query()->whereKey($id)->update([
            'hit_count' => DB::raw('hit_count + 1'),
            'last_hit_at' => now(),
        ]);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Résout la cible d'une redirection.
     *
     * Une cible absolue n'est suivie que si elle est en http(s) : sans ce
     * filtre, une entrée mal saisie — ou créée depuis un compte admin compromis
     * — pourrait pointer vers un schéma exotique (javascript:, data:) et
     * transformer une URL du site en vecteur d'hameçonnage.
     *
     * Tout le reste est ramené à un chemin interne à une seule barre oblique :
     * `url()` laisse passer les URL protocole-relatives (`//exemple.com`), qui
     * seraient sinon une redirection ouverte déguisée en chemin local.
     */
    private function resolveTarget(string $toPath): ?string
    {
        $scheme = parse_url($toPath, PHP_URL_SCHEME);

        if ($scheme === null) {
            return url('/'.ltrim($toPath, '/'));
        }

        return in_array(strtolower($scheme), ['http', 'https'], true) ? $toPath : null;
    }
}
