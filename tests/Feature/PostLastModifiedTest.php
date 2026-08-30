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

it('falls back to published_at when only touched by a bulk seo pass', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-07-06 17:28:38')])->saveQuietly();

    expect($post->fresh()->lastModifiedAt()->toDateString())->toBe('2019-06-21');
});

it('uses updated_at when the article was edited after the automated passes', function () {
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

it('hides any date on an evergreen article never revised', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-07-06 17:28:38')])->saveQuietly();

    $this->get(route('blog.show', $post->fresh()))
        ->assertOk()
        ->assertDontSee('Publié le')
        ->assertDontSee('21 juin 2019')
        ->assertDontSee('6 juillet 2026');
});

it('keeps the real publication date in the article schema', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-07-06 17:28:38')])->saveQuietly();

    $this->get(route('blog.show', $post->fresh()))
        ->assertOk()
        ->assertSee('2019-06-21T20:30:00', escape: false);
});

it('does not touch updated_at when a bulk command rewrites content', function () {
    $post = Post::factory()->create(['published_at' => Carbon::parse('2019-06-21 20:30:00')]);
    $post->forceFill(['updated_at' => Carbon::parse('2026-07-06 17:28:38')])->saveQuietly();

    Post::withoutTimestamps(
        fn () => $post->forceFill(['content' => '<p>Contenu réécrit.</p>'])->saveQuietly()
    );

    expect($post->fresh()->updated_at->toDateString())->toBe('2026-07-06');
});
