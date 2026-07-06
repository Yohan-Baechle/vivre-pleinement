<?php

use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows the health disclaimer and crisis numbers on articles', function () {
    $post = Post::factory()->create(['status' => 'published']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('3114')
        ->assertSee('prévention du suicide');
});

it('keeps the disclaimer, CTA and author card out of search snippets via data-nosnippet', function () {
    $post = Post::factory()->create(['status' => 'published']);

    $html = $this->get(route('blog.show', $post->slug))->assertOk()->getContent();

    expect(substr_count($html, 'data-nosnippet'))->toBe(3);
});

it('shows the health disclaimer and crisis numbers on video pages', function () {
    $video = Video::factory()->create();

    $this->get(route('videos.show', $video->slug))
        ->assertOk()
        ->assertSee('3114')
        ->assertSee('prévention du suicide');
});
