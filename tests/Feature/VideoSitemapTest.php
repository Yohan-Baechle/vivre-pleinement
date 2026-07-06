<?php

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

it('flushes both sitemap caches when a video is saved', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());
    Cache::put('sitemap.videos', ['cached'], now()->addHour());

    Video::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse()
        ->and(Cache::has('sitemap.videos'))->toBeFalse();
});
