<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureStripeWebhookIsSigned
{
    /**
     * Refuse les requêtes vers le webhook Cashier tant que le secret n'est pas
     * configuré.
     *
     * Cashier n'attache sa vérification de signature que si
     * `cashier.webhook.secret` est renseigné (WebhookController::__construct).
     * Sans secret, et le CSRF étant déjà désactivé sur ce préfixe,
     * /stripe/webhook devient un endpoint anonyme capable d'activer une
     * inscription à une formation ou de confirmer un rendez-vous payant. On
     * échoue donc en fermé.
     *
     * Seul le webhook est visé : /stripe/payment/{id}, la page de confirmation
     * 3-D Secure de Cashier, n'a rien à voir avec la signature et bloquer un
     * client au milieu de son authentification bancaire n'apporte rien.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim((string) config('cashier.path'), '/');

        if ($path === '' || ! $request->is($path.'/webhook')) {
            return $next($request);
        }

        if (blank(config('cashier.webhook.secret'))) {
            Log::critical('Requête Cashier refusée : STRIPE_WEBHOOK_SECRET est absent, le payload Stripe ne peut pas être authentifié.', [
                'path' => $request->path(),
            ]);

            abort(403, 'Webhook Stripe non configuré.');
        }

        return $next($request);
    }
}
