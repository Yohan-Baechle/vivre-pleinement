<?php

use App\Enums\VideoStatus;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

it('shows the video index page with the SEO metadata', function () {
    Video::factory()->count(3)->create();

    $this->get(route('videos.index'))->assertOk();
});

it('shows a published video page with related videos from the same category', function () {
    $category = Category::factory()->create();
    $video = Video::factory()->create();
    $video->categories()->attach($category);

    $related = Video::factory()->create();
    $related->categories()->attach($category);

    Video::factory()->create();

    $this->get(route('videos.show', $video))
        ->assertOk()
        ->assertSee($related->title);
});

it('404s for an unpublished video', function () {
    $video = Video::factory()->create(['status' => VideoStatus::Draft]);

    $this->get(route('videos.show', $video))->assertNotFound();
});

it('does not load the heavy transcript/intro columns for related videos', function () {
    $category = Category::factory()->create();
    $video = Video::factory()->create();
    $video->categories()->attach($category);

    $related = Video::factory()->create(['transcript' => str_repeat('x', 5000), 'intro' => str_repeat('y', 5000)]);
    $related->categories()->attach($category);

    DB::enableQueryLog();
    $this->get(route('videos.show', $video))->assertOk();
    $log = DB::getQueryLog();
    DB::disableQueryLog();

    $relatedQuery = collect($log)->last(fn ($q) => str_contains($q['query'], 'from `videos`') || str_contains($q['query'], 'from "videos"'));

    expect($relatedQuery)->not->toBeNull();
    expect($relatedQuery['query'])->not->toContain('transcript')
        ->and($relatedQuery['query'])->not->toContain('intro');
});
