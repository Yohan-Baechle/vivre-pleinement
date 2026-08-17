<?php

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('applies seo metadata, prepends the intro section and attaches tags to a targeted article', function () {
    $post = Post::factory()->create([
        'slug' => 'susceptibilite',
        'content' => '<p>Contenu original.</p>',
        'seo_title' => null,
        'seo_description' => null,
    ]);

    $this->artisan('seo:optimize-priority-articles')->assertSuccessful();

    $post->refresh();

    expect($post->seo_title)->toBe("Susceptibilité : Comment Arrêter d'Être Susceptible et S'en Libérer")
        ->and($post->seo_description)->toContain('La susceptibilité cache souvent')
        ->and($post->content)->toContain('<h2>Comment arrêter d\'être susceptible ?</h2>')
        ->and($post->content)->toContain('Contenu original.');

    $tagNames = Tag::query()->pluck('name')->all();

    expect($tagNames)->toContain('susceptibilité', 'confiance en soi', 'hypersensibilité', 'émotions')
        ->and($post->tags()->count())->toBe(4);
});

it('does not persist changes in dry-run mode', function () {
    $post = Post::factory()->create([
        'slug' => 'susceptibilite',
        'content' => '<p>Contenu original.</p>',
        'seo_title' => 'Titre original',
        'seo_description' => 'Description originale',
    ]);

    $this->artisan('seo:optimize-priority-articles', ['--dry-run' => true])->assertSuccessful();

    $post->refresh();

    expect($post->seo_title)->toBe('Titre original')
        ->and($post->seo_description)->toBe('Description originale')
        ->and($post->content)->toBe('<p>Contenu original.</p>')
        ->and(Tag::query()->count())->toBe(0);
});

it('warns and skips when a targeted article does not exist', function () {
    $this->artisan('seo:optimize-priority-articles')
        ->assertSuccessful()
        ->expectsOutputToContain('Article introuvable : susceptibilite');

    expect(Post::query()->count())->toBe(0);
});
