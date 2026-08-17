<?php

use App\Console\Commands\Videos\ApplySeoTitles;
use App\Models\Video;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('rewrites a YouTube title and locks the field against sync', function () {
    $video = Video::factory()->create([
        'slug' => 'comment-calmer-une-crise-dangoisse',
        'title' => 'Comment CALMER une CRISE D’ANGOISSE ?',
    ]);

    $this->artisan('videos:apply-seo-titles')->assertSuccessful();

    $video->refresh();

    expect($video->title)->toBe("Comment calmer une crise d'angoisse")
        ->and($video->isLocked('title'))->toBeTrue();
});

it('preserves existing locked fields when adding the title lock', function () {
    $video = Video::factory()->create([
        'slug' => 'comment-calmer-une-crise-dangoisse',
        'title' => 'Comment CALMER une CRISE D’ANGOISSE ?',
        'sync_locked_fields' => ['description'],
    ]);

    $this->artisan('videos:apply-seo-titles')->assertSuccessful();

    expect($video->refresh()->sync_locked_fields)->toBe(['description', 'title']);
});

it('changes nothing in dry-run mode', function () {
    $video = Video::factory()->create([
        'slug' => 'comment-calmer-une-crise-dangoisse',
        'title' => 'Comment CALMER une CRISE D’ANGOISSE ?',
    ]);

    $this->artisan('videos:apply-seo-titles --dry-run')->assertSuccessful();

    $video->refresh();

    expect($video->title)->toBe('Comment CALMER une CRISE D’ANGOISSE ?')
        ->and($video->isLocked('title'))->toBeFalse();
});

it('keeps every mapped title under 61 characters and free of emojis', function () {
    $command = new ApplySeoTitles;
    $method = new ReflectionMethod($command, 'titles');
    $titles = $method->invoke($command);

    expect($titles)->not->toBeEmpty();

    foreach ($titles as $slug => $title) {
        expect(mb_strlen($title))->toBeLessThanOrEqual(60, "Titre trop long pour {$slug}");
        expect(preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $title))->toBe(0, "Emoji détecté dans {$slug}");
    }
});
