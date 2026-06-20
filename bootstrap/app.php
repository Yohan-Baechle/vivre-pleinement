<?php

use App\Http\Middleware\HandleRedirects;
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
        // Le webhook Stripe (Cashier, préfixe cashier.path) ne doit pas exiger de CSRF.
        $middleware->validateCsrfTokens(except: ['stripe/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
