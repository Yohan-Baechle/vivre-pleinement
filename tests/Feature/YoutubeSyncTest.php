<?php

use App\Models\Video;
use App\Services\YoutubeSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeYoutubeApi(array $videoItems): void
{
    Http::fake([
        '*/youtube/v3/channels*' => Http::response([
            'items' => [
                ['contentDetails' => ['relatedPlaylists' => ['uploads' => 'UUuploads']]],
            ],
        ]),
        '*/youtube/v3/playlistItems*' => Http::response([
            'items' => collect($videoItems)
                ->map(fn (array $item) => ['contentDetails' => ['videoId' => $item['id']]])
                ->all(),
        ]),
        '*/youtube/v3/videos*' => Http::response(['items' => $videoItems]),
    ]);
}

it('creates a video from the youtube api', function () {
    fakeYoutubeApi([
        [
            'id' => 'abc123',
            'snippet' => [
                'title' => 'Gérer son anxiété',
                'description' => 'Une vidéo utile.',
                'publishedAt' => '2025-01-15T10:00:00Z',
                'thumbnails' => ['high' => ['url' => 'https://img/high.jpg']],
            ],
            'contentDetails' => ['duration' => 'PT1H2M3S'],
            'statistics' => ['viewCount' => '1200', 'likeCount' => '95'],
        ],
    ]);

    $result = (new YoutubeSync('test-key', 'UC_channel'))->sync();

    expect($result)->toMatchArray(['created' => 1, 'updated' => 0, 'total' => 1]);

    $video = Video::query()->firstOrFail();
    expect($video->youtube_id)->toBe('abc123')
        ->and($video->title)->toBe('Gérer son anxiété')
        ->and($video->slug)->toBe('gerer-son-anxiete')
        ->and($video->duration_seconds)->toBe(3723)
        ->and($video->view_count)->toBe(1200)
        ->and($video->published_at->toDateString())->toBe('2025-01-15');
});

it('updates an existing video but respects locked fields', function () {
    Video::factory()->create([
        'youtube_id' => 'abc123',
        'title' => 'Titre personnalisé',
        'sync_locked_fields' => ['title'],
        'view_count' => 0,
    ]);

    fakeYoutubeApi([
        [
            'id' => 'abc123',
            'snippet' => [
                'title' => 'Titre YouTube',
                'description' => 'Maj.',
                'publishedAt' => '2025-01-15T10:00:00Z',
                'thumbnails' => ['high' => ['url' => 'https://img/high.jpg']],
            ],
            'contentDetails' => ['duration' => 'PT10M'],
            'statistics' => ['viewCount' => '5000', 'likeCount' => '40'],
        ],
    ]);

    $result = (new YoutubeSync('test-key', 'UC_channel'))->sync();

    expect($result)->toMatchArray(['created' => 0, 'updated' => 1]);

    $video = Video::query()->firstOrFail();
    expect($video->title)->toBe('Titre personnalisé')
        ->and($video->view_count)->toBe(5000)
        ->and($video->duration_seconds)->toBe(600);
});

it('marks videos no longer returned by the api as missing', function () {
    Video::factory()->create(['youtube_id' => 'gone', 'is_missing' => false]);

    fakeYoutubeApi([
        [
            'id' => 'kept',
            'snippet' => ['title' => 'Toujours là', 'publishedAt' => '2025-01-15T10:00:00Z', 'thumbnails' => []],
            'contentDetails' => ['duration' => 'PT10M'],
            'statistics' => ['viewCount' => '1', 'likeCount' => '1'],
        ],
    ]);

    $result = (new YoutubeSync('test-key', 'UC_channel'))->sync();

    expect($result['missing'])->toBe(1)
        ->and(Video::query()->where('youtube_id', 'gone')->firstOrFail()->is_missing)->toBeTrue();
});

it('throws when the api is not configured', function () {
    (new YoutubeSync(null, null))->sync();
})->throws(RuntimeException::class);
