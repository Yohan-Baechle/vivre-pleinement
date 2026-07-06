<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('serves the ACT cornerstone page with its SEO essentials', function () {
    $html = $this->get('/therapie-act')->assertOk()->getContent();

    expect($html)->toContain('<title>Thérapie ACT : définition, principes et efficacité | Laura Baechlé</title>')
        ->and($html)->toContain('<link rel="canonical" href="'.route('therapie-act').'"')
        ->and($html)->toContain('"@type":"FAQPage"')
        ->and($html)->toContain('flexibilité psychologique')
        ->and(substr_count($html, '<h1'))->toBe(1);
});

it('links the cornerstone to the money pages and pillar articles', function () {
    $html = $this->get('/therapie-act')->assertOk()->getContent();

    expect($html)->toContain(route('booking.index'))
        ->and($html)->toContain(route('about'))
        ->and($html)->toContain('/blog/les-phobies-dimpulsion')
        ->and($html)->toContain('/blog/trouble-anxieux-generalise');
});

it('is listed in the sitemap', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('therapie-act'), false);
});
