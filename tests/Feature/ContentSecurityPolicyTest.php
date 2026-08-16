<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Vite;

uses(LazilyRefreshDatabase::class);

it('sends a content security policy on every response', function () {
    $policy = $this->get('/')->assertOk()->headers->get('Content-Security-Policy');

    expect($policy)->not->toBeNull()
        ->and($policy)->toContain("default-src 'self'")
        ->and($policy)->toContain("object-src 'none'")
        ->and($policy)->toContain("base-uri 'self'")
        ->and($policy)->toContain("form-action 'self'")
        ->and($policy)->toContain("frame-ancestors 'self'");
});

it('confines exfiltration channels to the site and Stripe', function () {
    $policy = $this->get('/')->headers->get('Content-Security-Policy');

    expect($policy)->toContain("connect-src 'self' https://api.stripe.com");
})->skip(fn () => Vite::isRunningHot(), 'Le serveur Vite élargit connect-src en local.');

it('allows the embeds the site actually needs', function () {
    $policy = $this->get('/')->headers->get('Content-Security-Policy');

    expect($policy)->toContain('https://js.stripe.com')
        ->and($policy)->toContain('https://www.youtube-nocookie.com')
        ->and($policy)->toContain('https://player.vimeo.com');
});

it('isolates the browsing context', function () {
    $this->get('/')->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
});
