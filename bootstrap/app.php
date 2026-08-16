<?php

use App\Http\Middleware\AuthenticateStudentSession;
use App\Http\Middleware\EnsureEnrolled;
use App\Http\Middleware\EnsureStripeWebhookIsSigned;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /**
         * Le site tourne derrière Cloudflare puis un reverse proxy local : sans
         * proxys de confiance, toutes les limitations d'envoi par IP se
         * confondent en un compteur unique et HSTS n'est jamais émis.
         *
         * La liste elle-même vit dans config/trustedproxy.php : le middleware
         * l'y lit à chaque requête, alors qu'ici la configuration n'est pas
         * encore chargée.
         */
        $middleware->trustProxies(
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        /**
         * Le Host sert à construire les URL absolues, dont certaines sont mises
         * en cache (sitemap) ou transmises à un tiers (Brevo) : on n'accepte
         * que les hôtes du site. Inerte en local et pendant les tests.
         */
        $middleware->trustHosts(at: fn (): array => config('security.trusted_hosts'));

        $middleware->prepend(EnsureStripeWebhookIsSigned::class);

        /**
         * SecureHeaders est appendé en premier pour envelopper HandleRedirects
         * : ce dernier remplace la réponse par une redirection neuve, qui doit
         * elle aussi porter la CSP et HSTS.
         */
        $middleware->append(SecureHeaders::class);
        $middleware->append(HandleRedirects::class);

        $middleware->appendToGroup('web', AuthenticateStudentSession::class);

        /**
         * Le webhook Stripe (Cashier, préfixe cashier.path) n'exige pas de CSRF
         * : sa signature est vérifiée par EnsureStripeWebhookIsSigned.
         */
        $middleware->validateCsrfTokens(except: ['stripe/*']);

        $middleware->alias([
            'enrolled' => EnsureEnrolled::class,
        ]);

        /**
         * Les élèves non connectés sont redirigés vers leur page de connexion ;
         * l'admin Filament gère sa propre redirection sur /espace-pro.
         */
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('espace-pro*')) {
                return null;
            }

            if ($request->is('youtube/*')) {
                return route('filament.admin.auth.login');
            }

            return route('student.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
