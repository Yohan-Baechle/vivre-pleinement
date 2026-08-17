<?php

use App\Models\Post;
use App\Models\Video;
use App\Support\Settings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows the author card under an article with a link to the bio page', function () {
    $post = Post::factory()->create(['status' => 'published']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Écrit par')
        ->assertSee('Laura Baechlé')
        ->assertSee('Praticienne ACT en accompagnement des troubles anxieux')
        ->assertSee(route('about'), false);
});

it('lists configured social profiles on the author card', function () {
    Settings::setMany(['social_youtube' => 'https://www.youtube.com/@LauraVivrePleinement']);
    $post = Post::factory()->create(['status' => 'published']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('https://www.youtube.com/@LauraVivrePleinement', false);
});

it('embeds the full author entity in the Article schema', function () {
    Settings::setMany(['social_youtube' => 'https://www.youtube.com/@LauraVivrePleinement']);
    $post = Post::factory()->create(['status' => 'published']);

    $html = $this->get(route('blog.show', $post->slug))->assertOk()->getContent();

    expect($html)->toContain('"jobTitle":"Praticienne ACT en accompagnement des troubles anxieux"')
        ->and($html)->toContain('"sameAs":["https://www.youtube.com/@LauraVivrePleinement"]');
});

it('embeds the full author entity in the VideoObject schema', function () {
    $video = Video::factory()->create();

    $html = $this->get(route('videos.show', $video->slug))->assertOk()->getContent();

    expect($html)->toContain('"jobTitle":"Praticienne ACT en accompagnement des troubles anxieux"');
});
