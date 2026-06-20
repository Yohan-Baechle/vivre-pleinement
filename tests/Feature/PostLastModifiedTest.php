<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('falls back to published_at when only touched by the migration import', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-05-25 10:21:00')])->saveQuietly();

    expect($post->fresh()->lastModifiedAt()->toDateString())->toBe('2019-06-21');
});

it('uses updated_at when the article was edited after the migration', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-08-01 09:00:00')])->saveQuietly();

    expect($post->fresh()->lastModifiedAt()->toDateString())->toBe('2026-08-01');
});
