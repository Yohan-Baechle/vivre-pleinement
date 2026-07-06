<?php

use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows the 3 most recent published articles and videos on the home page', function () {
    Post::factory()->count(4)->create();
    Post::factory()->draft()->create(['title' => 'Brouillon']);
    Video::factory()->count(4)->create();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertViewIs('home.index')
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 3)
        ->assertViewHas('videos', fn ($videos) => $videos->count() === 3)
        ->assertDontSee('Brouillon');
});
