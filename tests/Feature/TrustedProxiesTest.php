<?php

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

/**
 * Le site est servi derrière Cloudflare puis un reverse proxy local. Sans
 * proxys de confiance, `$request->ip()` renvoie l'adresse du proxy : toutes
 * les limitations d'envoi par IP se confondent en un compteur global unique et
 * `$request->secure()` reste faux, ce qui supprime l'en-tête HSTS.
 */
it('resolves the real visitor IP behind a Cloudflare edge', function () {
    $request = Request::create('https://vivre-pleinement.fr/contact', server: [
        'REMOTE_ADDR' => '162.158.1.1',
        'HTTP_X_FORWARDED_FOR' => '203.0.113.42',
    ]);

    handleThroughTrustProxies($request);

    expect($request->ip())->toBe('203.0.113.42');
});

it('treats a TLS-terminated request as secure so HSTS is emitted', function () {
    $request = Request::create('http://vivre-pleinement.fr/', server: [
        'REMOTE_ADDR' => '104.16.0.1',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ]);

    handleThroughTrustProxies($request);

    expect($request->isSecure())->toBeTrue();
});

it('ignores forwarded headers coming from an untrusted address', function () {
    $request = Request::create('http://vivre-pleinement.fr/', server: [
        'REMOTE_ADDR' => '203.0.113.7',
        'HTTP_X_FORWARDED_FOR' => '198.51.100.1',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ]);

    handleThroughTrustProxies($request);

    expect($request->ip())->toBe('203.0.113.7')
        ->and($request->isSecure())->toBeFalse();
});

it('lists the Cloudflare ranges and the local reverse proxy by default', function () {
    expect(config('trustedproxy.proxies'))
        ->toContain('127.0.0.1')
        ->toContain('::1')
        ->toContain('104.16.0.0/13')
        ->toContain('2400:cb00::/32');
});

function handleThroughTrustProxies(Request $request): void
{
    app(TrustProxies::class)->handle($request, fn () => response(''));
}
