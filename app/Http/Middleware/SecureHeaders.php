<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Directives de Content-Security-Policy.
     *
     * `script-src` doit tolérer 'unsafe-inline' (blocs JSON-LD, scripts
     * injectés par Livewire) et 'unsafe-eval' (Alpine évalue ses expressions
     * via `new Function`). La CSP ne bloque donc pas l'exécution d'un script
     * injecté ; sa valeur ici est de fermer les canaux d'exfiltration et de
     * détournement : `connect-src` et `form-action` empêchent l'envoi de
     * données vers un domaine tiers, `base-uri` neutralise l'injection de
     * <base>, `object-src` les plugins.
     *
     * @var array<string, list<string>>
     */
    private const CSP = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://js.stripe.com'],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'https://i.ytimg.com', 'https://*.stripe.com'],
        'font-src' => ["'self'", 'data:'],
        'connect-src' => ["'self'", 'https://api.stripe.com'],
        'frame-src' => [
            'https://js.stripe.com',
            'https://hooks.stripe.com',
            'https://www.youtube.com',
            'https://www.youtube-nocookie.com',
            'https://player.vimeo.com',
        ],
        'media-src' => ["'self'"],
        'worker-src' => ["'self'", 'blob:'],
        'manifest-src' => ["'self'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'self'"],
        'object-src' => ["'none'"],
    ];

    /**
     * Ajoute les en-têtes de sécurité sur toutes les réponses HTTP.
     *
     * HSTS n'est émis qu'en HTTPS pour ne pas polluer l'environnement local ;
     * X-Frame-Options reste en SAMEORIGIN car Filament ouvre des iframes
     * internes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Construit l'en-tête CSP.
     *
     * En local, le serveur de développement Vite sert les assets depuis une
     * autre origine et ouvre un websocket HMR : on élargit alors les directives
     * concernées plutôt que de retirer l'en-tête, pour que la politique reste
     * présente — et donc testable — dans tous les environnements.
     */
    private function contentSecurityPolicy(): string
    {
        $policy = self::CSP;

        foreach ($this->viteDevSources() as $directive => $sources) {
            $policy[$directive] = array_merge($policy[$directive] ?? [], $sources);
        }

        $directives = [];

        foreach ($policy as $directive => $sources) {
            $directives[] = $directive.' '.implode(' ', $sources);
        }

        return implode('; ', $directives);
    }

    /**
     * Origines supplémentaires à autoriser tant que le serveur Vite tourne.
     *
     * @return array<string, list<string>>
     */
    private function viteDevSources(): array
    {
        if (! Vite::isRunningHot()) {
            return [];
        }

        $origin = trim((string) @file_get_contents(Vite::hotFile()));

        if ($origin === '') {
            return [];
        }

        $websocket = str_replace(['https://', 'http://'], ['wss://', 'ws://'], $origin);

        return [
            'script-src' => [$origin],
            'style-src' => [$origin],
            'font-src' => [$origin],
            'img-src' => [$origin],
            'connect-src' => [$origin, $websocket],
        ];
    }
}
