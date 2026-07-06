<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows an article whose slug merely starts with a reserved word', function () {
    $post = Post::factory()->create(['slug' => 'rss-feed-review']);

    $this->get(route('blog.show', $post->slug))->assertOk();
});

it('shows an article whose slug starts with categorie without being the category route', function () {
    $post = Post::factory()->create(['slug' => 'categorie-de-produits-bio']);

    $this->get(route('blog.show', $post->slug))->assertOk();
});

it('still routes the exact reserved slugs to their dedicated pages', function () {
    $category = Category::factory()->create(['slug' => 'anxiete']);
    $tag = Tag::factory()->create(['slug' => 'act']);

    $this->get(route('blog.rss'))->assertOk();
    $this->get(route('blog.category', $category->slug))->assertOk();
    $this->get(route('blog.tag', $tag->slug))->assertOk();
});
