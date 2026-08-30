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
use Illuminate\Support\Facades\URL;
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
    'faq',
    'published_at',
])]
#[ObservedBy([PostObserver::class])]
class Post extends Model implements HasMedia
{
    /**
     * Directive robots d'une page indexable : le « max-snippet » hérité de
     * WordPress autorise les extraits longs et les grandes images, ce qui est
     * précisément ce qu'on veut sur un article.
     */
    public const ROBOTS_INDEXED = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

    /**
     * Directive robots d'une page retirée de l'index : les liens restent
     * suivis pour ne pas casser le maillage interne.
     */
    public const ROBOTS_HIDDEN = 'noindex, follow';

    /**
     * Borne haute des traitements automatisés passés : import WordPress puis
     * passes SEO en masse. Un updated_at antérieur ne correspond pas à une
     * vraie révision éditoriale. Les commandes écrivent désormais via
     * Post::withoutTimestamps(), cette borne n'a donc plus à bouger.
     */
    private const LAST_AUTOMATED_PASS_AT = '2026-07-06 23:59:59';

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasOptimizedMedia, InteractsWithMedia {
        HasOptimizedMedia::registerMediaConversions insteadof InteractsWithMedia;
    }
    use SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'comments_enabled' => true,
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'comments_enabled' => 'boolean',
            'seo_schema_json' => 'array',
            'faq' => 'array',
            'published_at' => 'datetime',
            'reading_time_minutes' => 'integer',
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
     * URL d'aperçu signée, valable deux heures, pour relire un brouillon dans
     * sa mise en page réelle sans avoir à le publier.
     */
    public function previewUrl(): string
    {
        return URL::temporarySignedRoute(
            'blog.preview',
            now()->addHours(2),
            ['post' => $this->getRouteKey()],
        );
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

    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)->where('published_at', '<=', now());
    }

    /**
     * Articles éligibles aux sitemaps : publiés et sans directive noindex.
     */
    public function scopeIndexable(Builder $query): void
    {
        $query->published()->where(function (Builder $query): void {
            $query->whereNull('seo_robots')->orWhere('seo_robots', 'not like', '%noindex%');
        });
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

    /**
     * Persisté dans reading_time_minutes (recalculé à chaque sauvegarde par
     * PostObserver) pour éviter de charger la colonne content (longText) dans
     * les listings juste pour ce calcul. Recalculé à la volée en repli si la
     * colonne n'est pas encore renseignée (ligne pas encore sauvegardée depuis
     * l'ajout de la colonne).
     */
    public function readingTimeMinutes(): int
    {
        if ($this->reading_time_minutes !== null) {
            return $this->reading_time_minutes;
        }

        return self::computeReadingTimeMinutes((string) $this->content);
    }

    public static function computeReadingTimeMinutes(string $content): int
    {
        $words = str_word_count(strip_tags($content));

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
     * Les articles migrés depuis WordPress puis retouchés par les passes SEO
     * en masse portent tous un updated_at de traitement : on retombe alors sur
     * published_at pour ne pas signaler à Google une modification fictive.
     * Une révision postérieure est respectée.
     */
    public function lastModifiedAt(): ?Carbon
    {
        if ($this->updated_at === null) {
            return $this->published_at;
        }

        return $this->updated_at->greaterThan(self::LAST_AUTOMATED_PASS_AT)
            ? $this->updated_at
            : $this->published_at;
    }

    /**
     * Vrai lorsque l'article a été révisé après sa publication. Le blog étant
     * du contenu froid, c'est la seule situation où une date est affichée :
     * une date de publication vieille de plusieurs années dessert un contenu
     * toujours valable.
     */
    public function wasUpdatedSincePublication(): bool
    {
        $modified = $this->lastModifiedAt();

        return $modified !== null
            && $this->published_at !== null
            && $modified->greaterThan($this->published_at);
    }
}
