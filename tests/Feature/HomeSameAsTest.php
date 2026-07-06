<?php

use App\Support\Settings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('adds sameAs to the Person schema when social links are configured', function () {
    Settings::setMany([
        'social_youtube' => 'https://www.youtube.com/@LauraVivrePleinement',
        'social_instagram' => 'https://www.instagram.com/laura.vivre.pleinement/',
        'social_linkedin' => 'https://www.linkedin.com/in/laura-baechl%C3%A9-180148210/',
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('"sameAs":')
        ->and($html)->toContain('youtube.com/@LauraVivrePleinement')
        ->and($html)->toContain('instagram.com/laura.vivre.pleinement')
        ->and($html)->toContain('linkedin.com/in/laura-baechl');
});

it('renders every configured social network in the footer, LinkedIn included', function () {
    Settings::setMany([
        'social_instagram' => 'https://www.instagram.com/laura.vivre.pleinement/',
        'social_facebook' => 'https://www.facebook.com/people/Laura-Vivre-Pleinement/100063529416248/',
        'social_youtube' => 'https://www.youtube.com/@LauraVivrePleinement',
        'social_tiktok' => 'https://www.tiktok.com/@laura.vivre.pleinement',
        'social_linkedin' => 'https://www.linkedin.com/in/laura-baechl%C3%A9-180148210/',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('aria-label="LinkedIn"', false)
        ->assertSee('aria-label="TikTok"', false)
        ->assertSee('https://www.linkedin.com/in/laura-baechl%C3%A9-180148210/', false);
});

it('omits sameAs when no social link is configured', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('"sameAs"');
});
