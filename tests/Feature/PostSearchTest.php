<?php

use App\Livewire\PostSearch;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('searches across title, excerpt and content', function () {
    Post::factory()->create(['title' => 'Vaincre l\'anxiété', 'excerpt' => 'x', 'content' => 'y']);
    Post::factory()->create(['title' => 'Autre', 'excerpt' => 'parlons de phobie ici', 'content' => 'z']);
    Post::factory()->create(['title' => 'Encore autre', 'excerpt' => 'x', 'content' => 'le mot burnout est dans le corps']);

    Livewire::test(PostSearch::class)
        ->set('search', 'anxiété')
        ->assertViewHas('posts', fn ($p) => $p->total() === 1)
        ->set('search', 'phobie')
        ->assertViewHas('posts', fn ($p) => $p->total() === 1)
        ->set('search', 'burnout')
        ->assertViewHas('posts', fn ($p) => $p->total() === 1);
});

it('shows the featured post only on the unfiltered default view', function () {
    Post::factory()->count(3)->create();

    Livewire::test(PostSearch::class)
        ->assertViewHas('featured', fn ($f) => $f !== null)
        ->set('search', 'x')
        ->assertViewHas('featured', fn ($f) => $f === null);
});

it('hides the featured post when sorting by oldest', function () {
    Post::factory()->count(3)->create();

    Livewire::test(PostSearch::class)
        ->set('sort', 'oldest')
        ->assertViewHas('featured', fn ($f) => $f === null);
});

it('filters by category from the url', function () {
    $category = Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $post = Post::factory()->create(['title' => 'Article phobie']);
    $post->categories()->attach($category);
    Post::factory()->create(['title' => 'Hors catégorie']);

    Livewire::test(PostSearch::class, ['category' => 'phobies'])
        ->assertViewHas('posts', fn ($p) => $p->total() === 1)
        ->assertSee('Article phobie')
        ->assertDontSee('Hors catégorie');
});

it('filters by tag from the url', function () {
    $tag = Tag::query()->firstOrCreate(['slug' => 'act'], ['name' => 'ACT']);
    $post = Post::factory()->create(['title' => 'Article ACT']);
    $post->tags()->attach($tag);
    Post::factory()->create(['title' => 'Sans tag']);

    Livewire::test(PostSearch::class, ['tag' => 'act'])
        ->assertViewHas('posts', fn ($p) => $p->total() === 1 && $p->first()->is($post));
});

it('removes an individual filter via the chips', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);

    Livewire::test(PostSearch::class, ['category' => 'phobies'])
        ->assertSet('category', 'phobies')
        ->call('removeFilter', 'category')
        ->assertSet('category', '');
});

it('clears all filters at once', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);

    Livewire::test(PostSearch::class, ['category' => 'phobies'])
        ->set('search', 'stress')
        ->call('clearAll')
        ->assertSet('search', '')
        ->assertSet('category', '');
});

it('escapes wildcard characters in the search term', function () {
    Post::factory()->create(['title' => 'Article normal']);

    // Un terme "%" ne doit pas tout matcher comme un joker SQL.
    Livewire::test(PostSearch::class)
        ->set('search', '%')
        ->assertViewHas('posts', fn ($p) => $p->total() === 0);
});

it('noindexes the search query on the blog page but indexes the listing', function () {
    $this->get('/blog')->assertDontSee('noindex', false);
    $this->get('/blog?q=anxiete')->assertSee('noindex', false);
});
