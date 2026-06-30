<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links a video to its article from the youtube description url', function () {
    $post = Post::factory()->create(['slug' => 'phobie-de-conduire']);
    $video = Video::factory()->create([
        'duration_seconds' => 600,
        'description' => "Ma vidéo sur la conduite.\nMon article : https://vivre-pleinement.fr/phobie-de-conduire/\nAbonnez-vous !",
    ]);

    $this->artisan('videos:link-related-posts')->assertSuccessful();

    expect($video->fresh()->related_post_id)->toBe($post->id);
});

it('ignores non-article paths like prendre-rendez-vous', function () {
    Post::factory()->create(['slug' => 'phobie-de-conduire']);
    $video = Video::factory()->create([
        'duration_seconds' => 600,
        'description' => 'Rdv : https://vivre-pleinement.fr/prendre-rendez-vous',
    ]);

    $this->artisan('videos:link-related-posts')->assertSuccessful();

    expect($video->fresh()->related_post_id)->toBeNull();
});

it('does not overwrite an existing link unless --force is passed', function () {
    $old = Post::factory()->create(['slug' => 'ancien']);
    $new = Post::factory()->create(['slug' => 'nouveau']);
    $video = Video::factory()->create([
        'duration_seconds' => 600,
        'related_post_id' => $old->id,
        'description' => 'https://vivre-pleinement.fr/nouveau/',
    ]);

    $this->artisan('videos:link-related-posts')->assertSuccessful();
    expect($video->fresh()->related_post_id)->toBe($old->id);

    $this->artisan('videos:link-related-posts', ['--force' => true])->assertSuccessful();
    expect($video->fresh()->related_post_id)->toBe($new->id);
});

it('shows the related article block on the video page', function () {
    $post = Post::factory()->create(['slug' => 'mon-article', 'title' => 'Mon article lié']);
    $video = Video::factory()->create([
        'slug' => 'ma-video',
        'duration_seconds' => 600,
        'related_post_id' => $post->id,
    ]);

    $this->get('/videos/ma-video')
        ->assertOk()
        ->assertSee('À lire aussi')
        ->assertSee('Mon article lié');
});

it('shows a topically relevant video block on the article page via fallback', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['slug' => 'article-cat', 'title' => 'Vaincre la cardiophobie au quotidien']);
    $post->categories()->attach($category);

    // Même catégorie mais titre proche : doit être choisie.
    $relevant = Video::factory()->create(['slug' => 'video-cardio', 'title' => 'La cardiophobie expliquée', 'duration_seconds' => 600, 'view_count' => 10]);
    $relevant->categories()->attach($category);

    // Même catégorie, très populaire, mais hors-sujet : ne doit PAS être choisie.
    $popular = Video::factory()->create(['slug' => 'video-popular', 'title' => 'Les antidépresseurs', 'duration_seconds' => 600, 'view_count' => 99999]);
    $popular->categories()->attach($category);

    expect($post->bestRelatedVideo()?->id)->toBe($relevant->id);

    $this->get('/blog/article-cat')
        ->assertOk()
        ->assertSee('La vidéo sur ce sujet');
});

it('shows no video block when no category video is topically relevant', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['slug' => 'article-orphelin', 'title' => 'La signification des rêves']);
    $post->categories()->attach($category);

    $offTopic = Video::factory()->create(['slug' => 'video-hs', 'title' => 'Les antidépresseurs et anxiolytiques', 'duration_seconds' => 600]);
    $offTopic->categories()->attach($category);

    expect($post->bestRelatedVideo())->toBeNull();

    $this->get('/blog/article-orphelin')
        ->assertOk()
        ->assertDontSee('La vidéo sur ce sujet');
});

it('prefers the explicitly linked video over the category fallback', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['slug' => 'article-pref']);
    $post->categories()->attach($category);

    $fallback = Video::factory()->create(['slug' => 'fallback', 'duration_seconds' => 600, 'view_count' => 9999]);
    $fallback->categories()->attach($category);

    $explicit = Video::factory()->create([
        'slug' => 'explicit',
        'duration_seconds' => 600,
        'view_count' => 1,
        'related_post_id' => $post->id,
    ]);

    expect($post->bestRelatedVideo()->id)->toBe($explicit->id);
});
