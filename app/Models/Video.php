<?php

namespace App\Models;

use App\Enums\VideoStatus;
use App\Observers\VideoObserver;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'youtube_id',
    'title',
    'slug',
    'description',
    'seo_description',
    'summary',
    'key_takeaways',
    'transcript',
    'chapters',
    'thumbnail_url',
    'youtube_published_at',
    'duration_seconds',
    'view_count',
    'like_count',
    'status',
    'published_at',
    'synced_at',
    'is_missing',
    'sync_locked_fields',
])]
#[ObservedBy([VideoObserver::class])]
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    use SoftDeletes;

    /** Champs qui peuvent être verrouillés contre la sync */
    public const LOCKABLE_FIELDS = ['title', 'description', 'thumbnail_url', 'slug'];

    /** Seuil au-dessus duquel une vidéo n'est plus considérée comme un Short YouTube. */
    public const SHORT_DURATION_THRESHOLD = 60;

    protected function casts(): array
    {
        return [
            'status' => VideoStatus::class,
            'duration_seconds' => 'integer',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'is_missing' => 'boolean',
            'sync_locked_fields' => 'array',
            'key_takeaways' => 'array',
            'chapters' => 'array',
            'youtube_published_at' => 'datetime',
            'published_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', VideoStatus::Published)
            ->where('is_missing', false)
            ->whereNotNull('published_at')
            ->where('duration_seconds', '>', self::SHORT_DURATION_THRESHOLD);
    }

    public function isShort(): bool
    {
        return $this->duration_seconds !== null
            && $this->duration_seconds <= self::SHORT_DURATION_THRESHOLD;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isLocked(string $field): bool
    {
        return in_array($field, $this->sync_locked_fields ?? [], true);
    }

    /**
     * Description méta pour le SEO. On s'appuie uniquement sur du contenu
     * rédigé pour le web (seo_description ou résumé) : jamais la description
     * YouTube brute, qui ferait doublon avec la page youtube.com. Sans source,
     * on retourne null et Google génère la méta depuis le contenu de la page.
     */
    public function metaDescription(int $limit = 160): ?string
    {
        $source = $this->seo_description ?: $this->summary;

        if (! $source) {
            return null;
        }

        return Str::limit(trim(strip_tags($source)), $limit);
    }

    public function hasEditorialContent(): bool
    {
        return filled($this->summary)
            || filled($this->transcript)
            || ! empty($this->key_takeaways);
    }

    /**
     * Chapitres formatés pour schema.org Clip[].
     *
     * @return list<array{name: string, startOffset: int, endOffset: int|null, url: string}>
     */
    public function chaptersForSchema(): array
    {
        $chapters = $this->chapters ?? [];
        if (empty($chapters)) {
            return [];
        }

        $count = count($chapters);
        $clips = [];

        foreach ($chapters as $i => $chapter) {
            $start = (int) ($chapter['start_seconds'] ?? 0);
            $title = trim((string) ($chapter['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $end = $i + 1 < $count
                ? (int) ($chapters[$i + 1]['start_seconds'] ?? 0)
                : $this->duration_seconds;

            $clips[] = [
                'name' => $title,
                'startOffset' => $start,
                'endOffset' => $end,
                'url' => $this->youtubeUrl().'&t='.$start.'s',
            ];
        }

        return $clips;
    }

    public function youtubeUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_id;
    }

    public function embedUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
    }

    public function thumbnail(string $size = 'maxres'): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        return "https://i.ytimg.com/vi/{$this->youtube_id}/{$size}default.jpg";
    }

    public function durationFormatted(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $h = intdiv($this->duration_seconds, 3600);
        $m = intdiv($this->duration_seconds % 3600, 60);
        $s = $this->duration_seconds % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }
}
