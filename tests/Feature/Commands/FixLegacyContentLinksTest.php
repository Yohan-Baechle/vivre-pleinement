<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('rewrites legacy root links that match an existing post', function () {
    Post::factory()->create(['slug' => 'burn-out-cible', 'status' => 'published']);
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<p>Lire <a href="/burn-out-cible">cet article</a> et <a href="https://vivre-pleinement.fr/burn-out-cible/">celui-ci</a>.</p>',
    ]);

    $this->artisan('posts:fix-legacy-links')->assertSuccessful();

    expect($post->fresh()->content)
        ->toContain('href="/blog/burn-out-cible"')
        ->not->toContain('href="/burn-out-cible"')
        ->not->toContain('vivre-pleinement.fr');
});

it('rewrites legacy category links only when the category exists', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<a href="/category/phobies">ok</a> <a href="/category/disparue">garde</a>',
    ]);

    $this->artisan('posts:fix-legacy-links')->assertSuccessful();

    expect($post->fresh()->content)
        ->toContain('href="/blog/categorie/phobies"')
        ->toContain('href="/category/disparue"');
});

it('leaves current site root paths and unknown slugs untouched', function () {
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<a href="/contact">contact</a> <a href="/reservation">rdv</a> <a href="/slug-inconnu">?</a> <a href="/blog/deja-bon">ok</a>',
    ]);

    $this->artisan('posts:fix-legacy-links')->assertSuccessful();

    expect($post->fresh()->content)->toBe($post->content);
});

it('writes nothing in dry-run mode', function () {
    Post::factory()->create(['slug' => 'cible', 'status' => 'published']);
    $post = Post::factory()->create([
        'status' => 'published',
        'content' => '<a href="/cible">lien</a>',
    ]);

    $this->artisan('posts:fix-legacy-links --dry-run')->assertSuccessful();

    expect($post->fresh()->content)->toContain('href="/cible"');
});
