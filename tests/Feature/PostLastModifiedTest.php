<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;

uses(LazilyRefreshDatabase::class);

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

it('shows the update date on an edited article', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-08-01 09:00:00')])->saveQuietly();

    $this->get(route('blog.show', $post->fresh()))
        ->assertOk()
        ->assertSee('Mis à jour le 1 août 2026')
        ->assertDontSee('21 juin 2019');
});

it('shows the publication date on an article never edited', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-05-25 10:21:00')])->saveQuietly();

    $this->get(route('blog.show', $post->fresh()))
        ->assertOk()
        ->assertSee('Publié le 21 juin 2019');
});
