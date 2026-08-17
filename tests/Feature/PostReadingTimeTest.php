<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

it('persists reading_time_minutes when content changes', function () {
    $post = Post::factory()->create(['content' => str_repeat('mot ', 230)]);

    expect($post->fresh()->reading_time_minutes)->toBe(1);

    $post->update(['content' => str_repeat('mot ', 460)]);

    expect($post->fresh()->reading_time_minutes)->toBe(2);
});

it('does not recompute reading time when other fields change', function () {
    $post = Post::factory()->create(['content' => str_repeat('mot ', 230)]);
    $original = $post->fresh()->reading_time_minutes;

    $post->update(['title' => 'Nouveau titre']);

    expect($post->fresh()->reading_time_minutes)->toBe($original);
});

it('does not load the content column for the category/tag blog listings', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['content' => str_repeat('x', 5000)]);
    $post->categories()->attach($category);

    DB::enableQueryLog();
    $this->get(route('blog.category', $category->slug))->assertOk();
    $log = DB::getQueryLog();
    DB::disableQueryLog();

    $postsQuery = collect($log)->first(fn ($q) => str_contains($q['query'], 'select * from `posts`') || str_contains($q['query'], 'select * from "posts"'));

    expect($postsQuery)->toBeNull();
});
