<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a long video page with its related videos', function () {
    $category = Category::factory()->create();

    $video = Video::factory()->create(['slug' => 'video-principale', 'duration_seconds' => 600]);
    $video->categories()->attach($category);

    // Vidéos similaires : leur relation categories est accédée par la video-card.
    Video::factory()->count(3)->create(['duration_seconds' => 600])
        ->each(fn ($v) => $v->categories()->attach($category));

    $this->get('/videos/video-principale')
        ->assertOk()
        ->assertSee('Vidéos similaires');
});

it('returns 404 for a short (the short has no dedicated page)', function () {
    Video::factory()->create(['slug' => 'mon-short', 'duration_seconds' => 40]);

    $this->get('/videos/mon-short')->assertNotFound();
});

it('lists only long videos on the index, never shorts', function () {
    Video::factory()->create(['slug' => 'longue', 'duration_seconds' => 300]);
    Video::factory()->create(['slug' => 'courte', 'duration_seconds' => 30]);

    $this->get('/videos')
        ->assertOk()
        ->assertSee('longue')
        ->assertDontSee('courte');
});
