<?php

use App\Http\Controllers\SitemapController;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

it('does not load the heavy transcript/intro columns for the video sitemap', function () {
    Video::factory()->create([
        'transcript' => str_repeat('x', 5000),
        'intro' => str_repeat('y', 5000),
    ]);

    DB::enableQueryLog();
    $this->get('/sitemap-videos.xml')->assertOk();
    $log = DB::getQueryLog();
    DB::disableQueryLog();

    $videosQuery = collect($log)->first(fn ($q) => str_contains($q['query'], 'from `videos`') || str_contains($q['query'], 'from "videos"'));

    expect($videosQuery)->not->toBeNull();
    expect($videosQuery['query'])->not->toContain('transcript')
        ->and($videosQuery['query'])->not->toContain('intro');
});

it('flushes the sitemap cache when a post is saved', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Post::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

/**
 * L'ancienne clé contenait une collection de modèles sérialisée. Elle
 * survit au déploiement dans le cache et ne doit plus être relue.
 */
it('ignores the legacy model-based video sitemap cache entry', function () {
    $video = Video::factory()->create();

    Cache::put('sitemap.videos', ['cached'], now()->addHour());

    $this->get('/sitemap-videos.xml')
        ->assertOk()
        ->assertSee(route('videos.show', $video), false);
});

/**
 * Le cache doit contenir du XML, jamais des modèles : une chaîne
 * traverse un changement de schéma sans devenir un objet incomplet.
 */
it('caches the rendered video sitemap as a string', function () {
    Video::factory()->create();

    $this->get('/sitemap-videos.xml')->assertOk();

    expect(Cache::get(SitemapController::VIDEOS_CACHE_KEY))->toBeString();
});

it('excludes noindexed videos from both sitemaps', function () {
    $indexable = Video::factory()->create();
    $hidden = Video::factory()->create(['seo_robots' => 'noindex, follow']);

    $this->get('/sitemap-videos.xml')
        ->assertOk()
        ->assertSee(route('videos.show', $indexable), false)
        ->assertDontSee(route('videos.show', $hidden), false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('videos.show', $indexable), false)
        ->assertDontSee(route('videos.show', $hidden), false);
});

it('renders a single noindex meta robots on a noindexed video page', function () {
    $video = Video::factory()->create(['seo_robots' => 'noindex, follow']);

    $html = $this->get(route('videos.show', $video->slug))->assertOk()->getContent();

    expect(substr_count($html, '<meta name="robots"'))->toBe(1)
        ->and($html)->toContain('noindex, follow');
});

it('does not emit content_loc for youtube-hosted videos', function () {
    Video::factory()->create();

    $this->get('/sitemap-videos.xml')
        ->assertOk()
        ->assertDontSee('video:content_loc', false)
        ->assertSee('video:player_loc', false);
});

it('flushes both sitemap caches when a video is saved', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());
    Cache::put(SitemapController::VIDEOS_CACHE_KEY, '<xml/>', now()->addHour());

    Video::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse()
        ->and(Cache::has(SitemapController::VIDEOS_CACHE_KEY))->toBeFalse();
});
