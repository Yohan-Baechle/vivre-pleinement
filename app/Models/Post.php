<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\HasOptimizedMedia;
use App\Observers\PostObserver;
use App\Support\Settings;
use App\Support\VideoArticleMatcher;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'status',
    'comments_enabled',
    'seo_title',
    'seo_description',
    'seo_canonical',
    'seo_robots',
    'seo_schema_json',
    'published_at',
])]
#[ObservedBy([PostObserver::class])]
class Post extends Model implements HasMedia
{
    /**
     * Borne haute de l'import WordPress : un updated_at antérieur correspond
     * à la migration, pas à une vraie édition.
     */
    private const MIGRATION_IMPORTED_AT = '2026-05-25 23:59:59';

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasOptimizedMedia, InteractsWithMedia {
        HasOptimizedMedia::registerMediaConversions insteadof InteractsWithMedia;
    }
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'comments_enabled' => 'boolean',
            'seo_schema_json' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Vidéos explicitement associées à cet article (même sujet).
     *
     * @return HasMany<Video, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'related_post_id');
    }

    /**
     * Meilleure vidéo à présenter sur l'article : la vidéo explicitement
     * associée en priorité, sinon la vidéo de la même catégorie dont le titre
     * est le plus proche thématiquement. Null si rien d'assez pertinent.
     */
    public function bestRelatedVideo(): ?Video
    {
        return VideoArticleMatcher::videoForPost($this);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)->where('published_at', '<=', now());
    }

    /**
     * Les commentaires sont-ils ouverts sur cet article ?
     * Requiert l'interrupteur global ET celui de l'article.
     */
    public function commentsAreOpen(): bool
    {
        return Settings::boolean('comments_enabled', true) && $this->comments_enabled;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * État du maillage interne de l'article, pour l'affichage dans l'admin :
     * - 'pillar' : article pilier de son cluster ;
     * - 'meshed' : rattaché à une catégorie (donc maillé) ;
     * - 'orphan' : aucune catégorie, ni similaires pertinents ni pilier.
     *
     * @return 'pillar'|'meshed'|'orphan'
     */
    public function meshStatus(): string
    {
        if ($this->categories->isEmpty()) {
            return 'orphan';
        }

        if ($this->categories->contains(fn (Category $category) => $category->pillar_post_id === $this->id)) {
            return 'pillar';
        }

        return 'meshed';
    }

    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return max(1, (int) ceil($words / 230));
    }

    /**
     * Extrait nettoyé pour l'affichage : retire le « […] » de troncature
     * ajouté par WordPress et coupe à la dernière phrase complète, pour
     * éviter une coupure en plein milieu de phrase.
     */
    public function cleanExcerpt(): string
    {
        $excerpt = trim((string) $this->excerpt);

        $excerpt = preg_replace('/\s*\[(?:…|\.\.\.)\]\s*$/u', '', $excerpt);
        $excerpt = trim($excerpt);

        if ($excerpt === '' || preg_match('/[.!?…]$/u', $excerpt)) {
            return $excerpt;
        }

        $excerpt = preg_replace('/\s*\S+$/u', '', $excerpt);
        $excerpt = rtrim($excerpt, ' ,;:–-');

        return $excerpt.' …';
    }

    public function featuredImageUrl(string $conversion = ''): ?string
    {
        return $this->getFirstMediaUrl('featured', $conversion) ?: null;
    }

    /**
     * Date de dernière modification réelle, pour le dateModified SEO.
     *
     * Les articles migrés depuis WordPress ont tous un updated_at à la date
     * d'import : on retombe alors sur published_at pour ne pas signaler à Google
     * une modification fictive. Une édition postérieure à l'import est respectée.
     */
    public function lastModifiedAt(): ?Carbon
    {
        if ($this->updated_at === null) {
            return $this->published_at;
        }

        return $this->updated_at->greaterThan(self::MIGRATION_IMPORTED_AT)
            ? $this->updated_at
            : $this->published_at;
    }
}
