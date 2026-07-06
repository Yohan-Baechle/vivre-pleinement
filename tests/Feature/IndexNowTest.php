<?php

use App\Models\Post;
use App\Support\IndexNow;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

it('pings the IndexNow API when a key is configured', function () {
    config(['services.indexnow.key' => 'test-key-123']);
    Http::fake();

    IndexNow::ping(url('/blog/test'));

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.indexnow.org/indexnow'
            && $request['key'] === 'test-key-123'
            && $request['urlList'] === [url('/blog/test')];
    });
});

it('stays silent without a configured key', function () {
    config(['services.indexnow.key' => null]);
    Http::fake();

    IndexNow::ping(url('/blog/test'));

    Http::assertNothingSent();
});

it('pings automatically when a published post is saved', function () {
    config(['services.indexnow.key' => 'test-key-123']);
    Http::fake();

    $post = Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);

    Http::assertSent(fn ($request) => in_array(route('blog.show', $post->slug), $request['urlList'], true));
});

it('does not ping for drafts', function () {
    config(['services.indexnow.key' => 'test-key-123']);
    Http::fake();

    Post::factory()->create(['status' => 'draft', 'published_at' => null]);

    Http::assertNothingSent();
});
