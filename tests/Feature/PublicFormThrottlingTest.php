<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

/**
 * M3 — SubmissionThrottle est consulté dans le contrôleur, donc après la
 * validation : la règle `email:rfc,dns` déclenchait une résolution DNS sur un
 * domaine choisi par l'appelant à chaque requête, sans plafond. Le middleware
 * `throttle` s'exécute avant la FormRequest et referme ce coin.
 */
it('plafonne les formulaires publics avant toute validation', function (string $routeName) {
    $middleware = collect(Route::getRoutes()->getByName($routeName)->gatherMiddleware());

    expect($middleware->contains(fn (string $name): bool => str_starts_with($name, 'throttle:')))
        ->toBeTrue();
})->with([
    'contact.send',
    'newsletter.store',
    'blog.comments.store',
    'student.login.store',
]);

it('coupe les envois massifs de contact sans exécuter la validation', function () {
    RateLimiter::clear('contact:127.0.0.1');

    $payload = [
        'first_name' => 'Camille',
        'email' => 'camille@gmail.com',
        'subject' => 'question',
        'message' => 'Bonjour, je souhaiterais en savoir plus sur votre accompagnement.',
        'consent' => '1',
        'website' => '',
        'ts' => submissionStamp(),
    ];

    foreach (range(1, 20) as $attempt) {
        $this->post(route('contact.send'), $payload);
    }

    $this->post(route('contact.send'), $payload)->assertStatus(429);
});
