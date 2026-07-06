<?php

use App\Console\Commands\TrimPostSeoDescriptions;
use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('trims an overlong meta description', function () {
    $post = Post::factory()->create([
        'slug' => 'etre-casanier',
        'seo_description' => str_repeat('Être casanier est souvent mal perçu. ', 5),
    ]);

    $this->artisan('posts:trim-seo-descriptions')->assertSuccessful();

    expect(mb_strlen($post->refresh()->seo_description))->toBeLessThanOrEqual(155);
});

it('changes nothing in dry-run mode', function () {
    $original = str_repeat('Être casanier est souvent mal perçu. ', 5);
    $post = Post::factory()->create(['slug' => 'etre-casanier', 'seo_description' => $original]);

    $this->artisan('posts:trim-seo-descriptions --dry-run')->assertSuccessful();

    expect($post->refresh()->seo_description)->toBe($original);
});

it('keeps every mapped description at 155 characters or fewer', function () {
    $command = new TrimPostSeoDescriptions;
    $method = new ReflectionMethod($command, 'descriptions');

    foreach ($method->invoke($command) as $slug => $description) {
        expect(mb_strlen($description))->toBeLessThanOrEqual(155, "Description trop longue pour {$slug}");
    }
});
