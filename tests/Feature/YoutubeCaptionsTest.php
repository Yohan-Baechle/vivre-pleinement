<?php

use App\Models\Video;
use App\Services\YoutubeCaptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.youtube.oauth_client_id', 'cid');
    config()->set('services.youtube.oauth_client_secret', 'secret');
    config()->set('services.youtube.oauth_refresh_token', 'refresh');
});

it('prefers the standard track over the asr track', function () {
    $captions = YoutubeCaptions::fromConfig();

    $tracks = [
        ['id' => 'asr-1', 'language' => 'fr', 'trackKind' => 'asr'],
        ['id' => 'std-1', 'language' => 'fr', 'trackKind' => 'standard'],
    ];

    expect($captions->pickBestTrackId($tracks))->toBe('std-1');
});

it('falls back to asr when no standard track exists', function () {
    $captions = YoutubeCaptions::fromConfig();

    $tracks = [['id' => 'asr-1', 'language' => 'fr', 'trackKind' => 'asr']];

    expect($captions->pickBestTrackId($tracks))->toBe('asr-1');
});

it('returns null when no track matches the language', function () {
    $captions = YoutubeCaptions::fromConfig();

    $tracks = [['id' => 'en-1', 'language' => 'en', 'trackKind' => 'standard']];

    expect($captions->pickBestTrackId($tracks))->toBeNull();
});

it('reports as configured only when all oauth keys are present', function () {
    expect(YoutubeCaptions::fromConfig()->isConfigured())->toBeTrue();

    config()->set('services.youtube.oauth_refresh_token', null);
    expect(YoutubeCaptions::fromConfig()->isConfigured())->toBeFalse();
});

it('fetches, cleans and stores a transcript from the srt subtitles', function () {
    $video = Video::factory()->create([
        'youtube_id' => 'abc123',
        'duration_seconds' => 600,
        'transcript' => null,
    ]);

    $srt = "1\n00:00:01,000 --> 00:00:04,000\nBonjour à tous.\n[Musique]\n\n"
        ."2\n00:00:04,000 --> 00:00:07,000\nAujourd'hui je vous parle d'anxiété.\n\n"
        ."3\n00:00:07,000 --> 00:00:09,000\nAujourd'hui je vous parle d'anxiété.\n";

    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/captions?*' => Http::response(['items' => [
            ['id' => 'cap-1', 'snippet' => ['language' => 'fr', 'trackKind' => 'standard']],
        ]]),
        '*/captions/cap-1*' => Http::response($srt),
    ]);

    $this->artisan('youtube:fetch-transcripts')->assertSuccessful();

    $video->refresh();

    expect($video->transcript)->not->toBeNull()
        ->and($video->transcript)->toContain('<p>')
        ->and($video->transcript)->toContain('Bonjour à tous')
        ->and($video->transcript)->toContain('anxiété')
        // L'annotation non verbale est retirée.
        ->and($video->transcript)->not->toContain('Musique')
        // La répétition consécutive est dédupliquée.
        ->and(substr_count($video->transcript, 'Aujourd'))->toBe(1);
});

it('skips a video that has no subtitles in the requested language', function () {
    $video = Video::factory()->create(['youtube_id' => 'nosubs', 'duration_seconds' => 600, 'transcript' => null]);

    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/captions?*' => Http::response(['items' => [
            ['id' => 'en-1', 'snippet' => ['language' => 'en', 'trackKind' => 'standard']],
        ]]),
    ]);

    $this->artisan('youtube:fetch-transcripts')->assertSuccessful();

    expect($video->fresh()->transcript)->toBeNull();
});

it('fails when oauth is not configured', function () {
    config()->set('services.youtube.oauth_refresh_token', null);

    $this->artisan('youtube:fetch-transcripts')->assertFailed();
});
