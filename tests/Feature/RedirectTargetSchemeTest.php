<?php

use App\Models\Redirect;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('rewrites a relative target onto the site domain', function () {
    Redirect::factory()->create([
        'from_path' => '/ancienne-page',
        'to_path' => '/blog',
        'status_code' => 301,
    ]);

    $this->get('/ancienne-page')->assertRedirect(url('/blog'));
});

it('follows an absolute https target', function () {
    Redirect::factory()->create([
        'from_path' => '/partenaire',
        'to_path' => 'https://example.com/page',
        'status_code' => 301,
    ]);

    $this->get('/partenaire')->assertRedirect('https://example.com/page');
});

it('keeps a protocol relative target on the site domain', function () {
    Redirect::factory()->create([
        'from_path' => '/relatif',
        'to_path' => '//example.com/phishing',
        'status_code' => 301,
    ]);

    $this->get('/relatif')->assertRedirect(url('/example.com/phishing'));
});

it('refuses a target using a non http scheme', function () {
    $redirect = Redirect::factory()->create([
        'from_path' => '/piege',
        'to_path' => 'javascript:alert(1)',
        'status_code' => 301,
    ]);

    $this->get('/piege')->assertNotFound();

    expect($redirect->fresh()->hit_count)->toBe(0);
});
