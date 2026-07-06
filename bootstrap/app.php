<?php

use App\Http\Middleware\EnsureEnrolled;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HandleRedirects::class);
        $middleware->append(SecureHeaders::class);
        // Le webhook Stripe (Cashier, préfixe cashier.path) ne doit pas exiger de CSRF.
        $middleware->validateCsrfTokens(except: ['stripe/*']);

        $middleware->alias([
            'enrolled' => EnsureEnrolled::class,
        ]);

        // Les élèves non connectés sont redirigés vers leur page de connexion ;
        // l'admin Filament gère sa propre redirection sur /espace-pro.
        $middleware->redirectGuestsTo(function ($request) {
            if (! $request->is('espace-pro*')) {
                return route('student.login');
            }
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
