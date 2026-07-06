<?php

use App\Support\Settings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('adds sameAs to the Person schema when social links are configured', function () {
    Settings::setMany([
        'social_youtube' => 'https://www.youtube.com/@laura.vivre.pleinement',
        'social_instagram' => 'https://www.instagram.com/laura.vivre.pleinement/',
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('"sameAs":')
        ->and($html)->toContain('youtube.com/@laura.vivre.pleinement')
        ->and($html)->toContain('instagram.com/laura.vivre.pleinement');
});

it('omits sameAs when no social link is configured', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('"sameAs"');
});
