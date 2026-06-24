<?php

namespace App\Services;

use App\Enums\VideoStatus;
use App\Models\Video;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class YoutubeSync
{
    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    private const RESERVED_SLUGS = ['rss', 'sitemap', 'edit', 'delete', 'create'];

    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $channelId,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            apiKey: config('services.youtube.api_key'),
            channelId: config('services.youtube.channel_id'),
        );
    }

    /**
     * Synchronise les vidéos longues de la chaîne configurée.
     *
     * Les Shorts (durée <= 60s) sont ignorés : ils ne sont jamais stockés. Le
     * paramètre $maxResults borne le nombre d'éléments parcourus dans la playlist
     * d'uploads (garde-fou), pas le nombre de vidéos conservées.
     *
     * @return array{created: int, updated: int, missing: int, total: int}
     */
    public function sync(int $maxResults = 500): array
    {
        $this->ensureConfigured();

        $uploadsPlaylistId = $this->fetchUploadsPlaylistId();
        $videoIds = $this->fetchPlaylistVideoIds($uploadsPlaylistId, $maxResults);

        if (empty($videoIds)) {
            $missing = $this->markMissingVideos(collect());

            return ['created' => 0, 'updated' => 0, 'missing' => $missing, 'total' => 0];
        }

        $videosData = $this->rejectShorts($this->fetchVideosDetails($videoIds));
        $fetchedIds = collect($videosData)->pluck('id');

        $existingByYtId = Video::query()
            ->withTrashed()
            ->whereIn('youtube_id', $fetchedIds)
            ->get()
            ->keyBy('youtube_id');

        $created = 0;
        $updated = 0;

        foreach ($videosData as $data) {
            /** @var Video|null $video */
            $video = $existingByYtId->get($data['id']);

            if ($video) {
                $this->updateVideo($video, $data);
                $updated++;
            } else {
                $this->createVideo($data);
                $created++;
            }
        }

        $missing = $this->markMissingVideos($fetchedIds);

        return [
            'created' => $created,
            'updated' => $updated,
            'missing' => $missing,
            'total' => count($videosData),
        ];
    }

    private function createVideo(array $data): void
    {
        $publishedAt = $this->parsePublishedAt($data);

        Video::create([
            'youtube_id' => $data['id'],
            'title' => $data['snippet']['title'],
            'slug' => $this->makeSlug($data['snippet']['title'], $data['id']),
            'description' => $data['snippet']['description'] ?? null,
            'thumbnail_url' => $this->bestThumbnail($data['snippet']['thumbnails'] ?? []),
            'duration_seconds' => $this->parseIso8601Duration($data['contentDetails']['duration'] ?? null),
            'view_count' => $data['statistics']['viewCount'] ?? null,
            'like_count' => $data['statistics']['likeCount'] ?? null,
            'youtube_published_at' => $publishedAt,
            'published_at' => $publishedAt,
            'status' => VideoStatus::Published,
            'is_missing' => false,
            'synced_at' => now(),
        ]);
    }

    private function updateVideo(Video $video, array $data): void
    {
        $attributes = [
            'view_count' => $data['statistics']['viewCount'] ?? null,
            'like_count' => $data['statistics']['likeCount'] ?? null,
            'duration_seconds' => $this->parseIso8601Duration($data['contentDetails']['duration'] ?? null),
            'youtube_published_at' => $this->parsePublishedAt($data) ?? $video->youtube_published_at,
            'is_missing' => false,
            'synced_at' => now(),
        ];

        if (! $video->isLocked('title')) {
            $attributes['title'] = $data['snippet']['title'];
        }
        if (! $video->isLocked('description')) {
            $attributes['description'] = $data['snippet']['description'] ?? null;
        }
        if (! $video->isLocked('thumbnail_url')) {
            $attributes['thumbnail_url'] = $this->bestThumbnail($data['snippet']['thumbnails'] ?? []);
        }

        $video->update($attributes);

        if ($video->trashed()) {
            $video->restore();
        }
    }

    /**
     * Marque comme "missing" les vidéos en base qui ne sont plus retournées par l'API.
     */
    private function markMissingVideos(Collection $fetchedIds): int
    {
        $missingIds = Video::query()
            ->whereNotIn('youtube_id', $fetchedIds)
            ->where('is_missing', false)
            ->pluck('id');

        if ($missingIds->isEmpty()) {
            return 0;
        }

        Video::whereIn('id', $missingIds)->update([
            'is_missing' => true,
            'synced_at' => now(),
        ]);

        Log::info('YouTube sync : vidéos marquées comme manquantes', [
            'count' => $missingIds->count(),
            'ids' => $missingIds->all(),
        ]);

        return $missingIds->count();
    }

    private function ensureConfigured(): void
    {
        if (! $this->apiKey || ! $this->channelId) {
            throw new RuntimeException(
                'YouTube API non configurée. Renseignez YOUTUBE_API_KEY et YOUTUBE_CHANNEL_ID dans .env.'
            );
        }
    }

    private function fetchUploadsPlaylistId(): string
    {
        $response = $this->client()->get(self::API_BASE.'/channels', [
            'part' => 'contentDetails',
            'id' => $this->channelId,
            'key' => $this->apiKey,
        ]);

        $playlistId = data_get($response->json(), 'items.0.contentDetails.relatedPlaylists.uploads');

        if (! $playlistId) {
            Log::warning('YouTube channel introuvable', ['channel_id' => $this->channelId]);
            throw new RuntimeException("Chaîne YouTube introuvable (ID: {$this->channelId}).");
        }

        return $playlistId;
    }

    /**
     * @return list<string>
     */
    private function fetchPlaylistVideoIds(string $playlistId, int $maxResults): array
    {
        $ids = [];
        $pageToken = null;
        $perPage = 50;

        do {
            $params = [
                'part' => 'contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => min($perPage, $maxResults - count($ids)),
                'key' => $this->apiKey,
            ];
            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = $this->client()->get(self::API_BASE.'/playlistItems', $params);
            $payload = $response->json();

            foreach ($payload['items'] ?? [] as $item) {
                $id = data_get($item, 'contentDetails.videoId');
                if ($id) {
                    $ids[] = $id;
                }
                if (count($ids) >= $maxResults) {
                    break 2;
                }
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while ($pageToken);

        return $ids;
    }

    /**
     * Écarte les Shorts (durée <= seuil) afin qu'ils ne soient jamais stockés.
     * Une durée absente/non parsable est conservée par prudence (sera traitée
     * comme vidéo longue plutôt que supprimée à tort).
     *
     * @param  list<array<string, mixed>>  $videosData
     * @return list<array<string, mixed>>
     */
    private function rejectShorts(array $videosData): array
    {
        return array_values(array_filter($videosData, function (array $data): bool {
            $duration = $this->parseIso8601Duration($data['contentDetails']['duration'] ?? null);

            return $duration === null || $duration > Video::SHORT_DURATION_THRESHOLD;
        }));
    }

    /**
     * @param  list<string>  $ids
     * @return list<array<string, mixed>>
     */
    private function fetchVideosDetails(array $ids): array
    {
        $details = [];

        foreach (array_chunk($ids, 50) as $chunk) {
            $response = $this->client()->get(self::API_BASE.'/videos', [
                'part' => 'snippet,contentDetails,statistics',
                'id' => implode(',', $chunk),
                'key' => $this->apiKey,
            ]);

            foreach ($response->json('items') ?? [] as $item) {
                $details[] = $item;
            }
        }

        return $details;
    }

    private function client(): PendingRequest
    {
        return Http::timeout(15)->retry(2, 250)->acceptJson()->throw();
    }

    /**
     * @param  array<string, array{url: string, width: int, height: int}>  $thumbnails
     */
    private function bestThumbnail(array $thumbnails): ?string
    {
        foreach (['maxres', 'standard', 'high', 'medium', 'default'] as $size) {
            if (isset($thumbnails[$size]['url'])) {
                return $thumbnails[$size]['url'];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function parsePublishedAt(array $data): ?Carbon
    {
        $value = $data['snippet']['publishedAt'] ?? null;

        return $value ? Carbon::parse($value) : null;
    }

    private function parseIso8601Duration(?string $duration): ?int
    {
        if (! $duration) {
            return null;
        }

        try {
            $interval = new \DateInterval($duration);
        } catch (\Exception) {
            return null;
        }

        return $interval->h * 3600 + $interval->i * 60 + $interval->s;
    }

    /**
     * Génère un slug unique safe contre slugs réservés et titres vides.
     */
    private function makeSlug(string $title, string $youtubeId): string
    {
        $base = Str::slug($title);
        if ($base === '' || in_array($base, self::RESERVED_SLUGS, true)) {
            $base = 'video-'.Str::lower(Str::substr($youtubeId, 0, 6));
        }

        $slug = $base;
        $i = 2;
        while (Video::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
            if ($i > 100) {
                $slug = $base.'-'.Str::lower(Str::substr($youtubeId, 0, 8));
                break;
            }
        }

        return $slug;
    }
}
