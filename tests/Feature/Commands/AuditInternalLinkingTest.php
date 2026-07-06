<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('reports a healthy blog with no linking problems', function () {
    $pillar = Post::factory()->create(['status' => PostStatus::Published]);
    $category = Category::factory()->create(['pillar_post_id' => $pillar->id]);
    $category->posts()->attach($pillar);

    $this->artisan('seo:maillage')
        ->assertSuccessful()
        ->expectsOutputToContain('Maillage interne sain : aucun problème détecté.');
});

it('flags a published article with no category as an orphan', function () {
    $orphan = Post::factory()->create(['status' => PostStatus::Published, 'slug' => 'article-orphelin']);

    $this->artisan('seo:maillage')
        ->assertFailed()
        ->expectsOutputToContain('article-orphelin');
});

it('flags a category with posts but no pillar article', function () {
    $category = Category::factory()->create(['slug' => 'sans-pilier', 'pillar_post_id' => null]);
    $post = Post::factory()->create(['status' => PostStatus::Published]);
    $category->posts()->attach($post);

    $this->artisan('seo:maillage')
        ->assertFailed()
        ->expectsOutputToContain('sans-pilier');
});

it('flags a category whose pillar article is unpublished', function () {
    $pillar = Post::factory()->create(['status' => PostStatus::Draft]);
    $category = Category::factory()->create(['slug' => 'pilier-invalide', 'pillar_post_id' => $pillar->id]);
    $category->posts()->attach($pillar);

    $this->artisan('seo:maillage')
        ->assertFailed()
        ->expectsOutputToContain("pilier-invalide — pilier #{$pillar->id} invalide");
});

it('flags a category whose pillar article does not belong to it', function () {
    $pillar = Post::factory()->create(['status' => PostStatus::Published]);
    $category = Category::factory()->create(['slug' => 'pilier-hors-categorie', 'pillar_post_id' => $pillar->id]);

    $this->artisan('seo:maillage')
        ->assertFailed()
        ->expectsOutputToContain("pilier-hors-categorie — pilier #{$pillar->id} invalide");
});
