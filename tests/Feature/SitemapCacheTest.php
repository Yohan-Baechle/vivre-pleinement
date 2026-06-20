<?php

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('flushes the sitemap cache when a category changes', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Category::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

it('flushes the sitemap cache when a tag changes', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Tag::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

it('lists the indexable legal pages in the sitemap', function () {
    Cache::flush();

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('legal.mentions'), false)
        ->assertSee(route('legal.privacy'), false)
        ->assertSee(route('legal.cookies'), false)
        ->assertSee(route('legal.cgv'), false);
});

it('keeps legal pages indexable (no robots noindex)', function () {
    $this->get('/mentions-legales')
        ->assertOk()
        ->assertDontSee('noindex', false);
});
