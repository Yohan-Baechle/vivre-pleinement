<?php

use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

/**
 * Le cache doit contenir du XML, jamais des modèles : une chaîne
 * traverse un changement de schéma sans devenir un objet incomplet.
 */
it('caches the rendered feed as a string', function () {
    Post::factory()->create();

    $this->get(route('blog.rss'))->assertOk();

    expect(Cache::get(PostController::RSS_CACHE_KEY))->toBeString();
});

it('flushes the feed cache when a post is saved', function () {
    Cache::put(PostController::RSS_CACHE_KEY, '<xml/>', now()->addHour());

    Post::factory()->create();

    expect(Cache::has(PostController::RSS_CACHE_KEY))->toBeFalse();
});

/**
 * L'ancienne clé contenait une collection de modèles sérialisée. Elle
 * survit au déploiement dans le cache et ne doit plus être relue.
 */
it('ignores the legacy model-based feed cache entry', function () {
    $post = Post::factory()->create();

    Cache::put('blog.rss.posts', ['cached'], now()->addHour());

    $this->get(route('blog.rss'))
        ->assertOk()
        ->assertSee($post->title, false);
});
