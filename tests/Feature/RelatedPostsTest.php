<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\InternalLinking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('suggests posts from the same category cluster', function () {
    $phobies = Category::factory()->create();
    $autre = Category::factory()->create();

    $current = Post::factory()->create(['slug' => 'phobie-avion']);
    $current->categories()->attach($phobies);

    foreach (['aquaphobie', 'cardiophobie', 'agoraphobie'] as $slug) {
        Post::factory()->create(['slug' => $slug])->categories()->attach($phobies);
    }
    Post::factory()->create(['slug' => 'recette'])->categories()->attach($autre);

    $similar = $this->get('/blog/phobie-avion')->assertOk()->viewData('similar');

    expect($similar)->toHaveCount(3)
        ->and($similar->pluck('slug')->all())->each->toBeIn(['aquaphobie', 'cardiophobie', 'agoraphobie']);
});

it('ranks same-cluster posts by shared tags first', function () {
    $cluster = Category::factory()->create();
    $tag = Tag::factory()->create();

    $current = Post::factory()->create(['slug' => 'reference']);
    $current->categories()->attach($cluster);
    $current->tags()->attach($tag);

    $proche = Post::factory()->create(['slug' => 'proche', 'published_at' => now()->subYear()]);
    $proche->categories()->attach($cluster);
    $proche->tags()->attach($tag); // partage un tag

    $loin = Post::factory()->create(['slug' => 'loin', 'published_at' => now()]);
    $loin->categories()->attach($cluster); // plus récent mais aucun tag partagé

    $similar = $this->get('/blog/reference')->assertOk()->viewData('similar');

    expect($similar->first()->slug)->toBe('proche');
});

it('fills the related block when the category has few posts', function () {
    $cluster = Category::factory()->create();

    $current = Post::factory()->create(['slug' => 'seul-dans-son-cluster']);
    $current->categories()->attach($cluster);

    Post::factory()->count(3)->create();

    $similar = $this->get('/blog/seul-dans-son-cluster')->assertOk()->viewData('similar');

    expect($similar->count())->toBe(3);
});

it('shows a "Pour aller plus loin" link to the category pillar', function () {
    $cluster = Category::factory()->create();

    $pillar = Post::factory()->create(['slug' => 'phobie-sociale', 'title' => 'PilierPhobies']);
    $pillar->categories()->attach($cluster);

    $article = Post::factory()->create(['slug' => 'aquaphobie']);
    $article->categories()->attach($cluster);

    $cluster->update(['pillar_post_id' => $pillar->id]);

    $this->get('/blog/aquaphobie')->assertOk()->assertSee('PilierPhobies');
});

it('does not link the pillar to itself', function () {
    $cluster = Category::factory()->create();

    $pillar = Post::factory()->create(['slug' => 'phobie-sociale']);
    $pillar->categories()->attach($cluster);
    $cluster->update(['pillar_post_id' => $pillar->id]);

    $resolved = $this->get('/blog/phobie-sociale')->assertOk()->viewData('pillar');

    expect($resolved)->toBeNull();
});

it('caches the similar posts and invalidates them when a post in the cluster changes', function () {
    $cluster = Category::factory()->create();

    $current = Post::factory()->create(['slug' => 'reference']);
    $current->categories()->attach($cluster);

    $first = Post::factory()->create(['slug' => 'voisin']);
    $first->categories()->attach($cluster);

    InternalLinking::similar($current->fresh('categories', 'tags'));
    expect(Cache::has("blog.linking.similar.{$current->id}"))->toBeTrue();

    $first->update(['title' => 'Titre modifié']);
    expect(Cache::has("blog.linking.similar.{$current->id}"))->toBeFalse();
});

it('returns real Post models on a second (cached) read', function () {
    $cluster = Category::factory()->create();

    $current = Post::factory()->create(['slug' => 'reference']);
    $current->categories()->attach($cluster);
    Post::factory()->count(2)->create()->each(fn ($p) => $p->categories()->attach($cluster));

    $loaded = fn () => InternalLinking::similar($current->fresh('categories', 'tags'));

    $first = $loaded();
    $second = $loaded();

    expect($second)->toHaveCount($first->count())
        ->and($second->first())->toBeInstanceOf(Post::class)
        ->and($second->pluck('id')->all())->toBe($first->pluck('id')->all());
});

it('reports a healthy mesh when everything is wired', function () {
    $cluster = Category::factory()->create();
    $pillar = Post::factory()->create();
    $pillar->categories()->attach($cluster);
    $cluster->update(['pillar_post_id' => $pillar->id]);

    $this->artisan('seo:maillage')->assertExitCode(0);
});

it('fails the audit when a published post is orphaned', function () {
    Post::factory()->create(['slug' => 'orphelin']); // aucune catégorie

    $this->artisan('seo:maillage')
        ->expectsOutputToContain('orphelin')
        ->assertExitCode(1);
});

it('fails the audit when a category has no pillar', function () {
    $cluster = Category::factory()->create();
    Post::factory()->create()->categories()->attach($cluster);

    $this->artisan('seo:maillage')->assertExitCode(1);
});
