<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureAutomationTokenIsValid
{
    /**
     * Réserve les endpoints d'automatisation (n8n) au porteur du jeton
     * AUTOMATION_TOKEN.
     *
     * Sans jeton configuré, on échoue en fermé : ces routes écrivent du
     * contenu publié et ne doivent jamais devenir anonymes par oubli.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.automation.token');

        if ($expected === '') {
            Log::critical(
                'Requête d\'automatisation refusée : AUTOMATION_TOKEN absent.',
                ['path' => $request->path()],
            );

            abort(403, 'Automatisation non configurée.');
        }

        if (! hash_equals($expected, (string) $request->bearerToken())) {
            abort(401);
        }

        return $next($request);
    }
}
