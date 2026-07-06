<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('serves llms.txt as plain text with the key pages', function () {
    $this->get('/llms.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('# Vivre Pleinement', false)
        ->assertSee(route('booking.index'), false)
        ->assertSee(route('courses.index'), false)
        ->assertSee(route('book.show'), false);
});

it('lists blog categories and existing pillar posts', function () {
    $category = Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $post = Post::factory()->create(['slug' => 'burn-out', 'status' => 'published', 'title' => 'Burn-out']);

    $this->get('/llms.txt')
        ->assertOk()
        ->assertSee(route('blog.category', $category->slug), false)
        ->assertSee(route('blog.show', $post), false);
});
