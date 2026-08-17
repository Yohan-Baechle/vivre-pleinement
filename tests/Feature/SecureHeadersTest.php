<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('sends the security headers on every response', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

it('only sends HSTS over https', function () {
    expect($this->get('/')->headers->has('Strict-Transport-Security'))->toBeFalse();

    $secure = $this->get('https://localhost/');

    expect($secure->headers->get('Strict-Transport-Security'))
        ->toBe('max-age=31536000; includeSubDomains');
});
