<?php

use App\Livewire\VideoSearch;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters videos by a search term across title, summary and intro', function () {
    Video::factory()->create(['title' => 'La peur de conduire', 'duration_seconds' => 600]);
    Video::factory()->create(['title' => 'Le sommeil réparateur', 'summary' => 'Comment vaincre l\'insomnie', 'duration_seconds' => 600]);
    Video::factory()->create(['title' => 'Autre sujet', 'intro' => '<p>Texte sur la respiration</p>', 'duration_seconds' => 600]);

    Livewire::test(VideoSearch::class)
        ->set('search', 'conduire')
        ->assertViewHas('videos', fn ($videos) => $videos->total() === 1)
        ->assertSee('La peur de conduire')
        ->assertDontSee('Le sommeil réparateur');
});

it('finds a video by a word in its summary', function () {
    Video::factory()->create(['title' => 'Le sommeil', 'summary' => 'Comment vaincre l\'insomnie', 'duration_seconds' => 600]);

    Livewire::test(VideoSearch::class)
        ->set('search', 'insomnie')
        ->assertViewHas('videos', fn ($videos) => $videos->total() === 1);
});

it('filters by category', function () {
    $phobies = Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $video = Video::factory()->create(['title' => 'Une phobie', 'duration_seconds' => 600]);
    $video->categories()->attach($phobies);
    Video::factory()->create(['title' => 'Hors catégorie', 'duration_seconds' => 600]);

    Livewire::test(VideoSearch::class)
        ->call('selectCategory', 'phobies')
        ->assertViewHas('videos', fn ($videos) => $videos->total() === 1)
        ->assertSee('Une phobie')
        ->assertDontSee('Hors catégorie');
});

it('combines search and category filter', function () {
    $phobies = Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $match = Video::factory()->create(['title' => 'Peur de conduire', 'duration_seconds' => 600]);
    $match->categories()->attach($phobies);
    $wrongCategory = Video::factory()->create(['title' => 'Peur de conduire (sans catégorie)', 'duration_seconds' => 600]);

    Livewire::test(VideoSearch::class)
        ->set('category', 'phobies')
        ->set('search', 'conduire')
        ->assertViewHas('videos', fn ($videos) => $videos->total() === 1 && $videos->first()->is($match));
});

it('toggling the same category clears the filter', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);

    Livewire::test(VideoSearch::class)
        ->call('selectCategory', 'phobies')
        ->assertSet('category', 'phobies')
        ->call('selectCategory', 'phobies')
        ->assertSet('category', '');
});

it('clears the search term', function () {
    Livewire::test(VideoSearch::class)
        ->set('search', 'phobie')
        ->call('clearSearch')
        ->assertSet('search', '');
});

it('never lists shorts', function () {
    Video::factory()->create(['title' => 'Une longue vidéo', 'duration_seconds' => 600]);
    Video::factory()->create(['title' => 'Un short', 'duration_seconds' => 30]);

    Livewire::test(VideoSearch::class)
        ->assertSee('Une longue vidéo')
        ->assertDontSee('Un short');
});

it('noindexes search result pages but indexes the listing and categories', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);

    $this->get('/videos')->assertDontSee('noindex', false);
    $this->get('/videos?category=phobies')->assertDontSee('noindex', false);
    $this->get('/videos?q=phobie')->assertSee('noindex', false);
});
