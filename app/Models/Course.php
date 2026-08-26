<?php

namespace App\Models;

use App\Enums\CourseStatus;
use App\Models\Concerns\HasOptimizedMedia;
use App\Models\Concerns\HasPriceInCents;
use App\Observers\CourseObserver;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([CourseObserver::class])]
#[Fillable([
    'title',
    'slug',
    'subtitle',
    'description',
    'outcomes',
    'price',
    'price_cents',
    'currency',
    'intro_video_provider',
    'intro_video_id',
    'level',
    'duration_minutes',
    'status',
    'published_at',
    'seo_title',
    'seo_description',
    'position',
])]
class Course extends Model implements HasMedia
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    use HasOptimizedMedia, InteractsWithMedia {
        HasOptimizedMedia::registerMediaConversions insteadof InteractsWithMedia;
    }
    use HasPriceInCents;
    use SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'EUR',
        'status' => 'draft',
        'position' => 0,
    ];

    protected function casts(): array
    {
        return [
            'outcomes' => 'array',
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    /**
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    /**
     * @return HasManyThrough<Lesson, Module, $this>
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Indique qu'au moins une formation est publiée.
     *
     * Le menu et le sitemap s'en servent pour ne pas exposer un espace vide :
     * sans formation, ni l'entrée « Formations » ni la connexion élève n'ont
     * de destination, l'espace élève ne servant qu'aux formations.
     *
     * Volontairement sans cache : un `exists()` indexé sur une table de
     * quelques lignes ne coûte rien, alors qu'une valeur mémorisée se périme
     * et rend la bascule dépendante d'une invalidation à ne pas oublier.
     */
    public static function hasPublished(): bool
    {
        return static::query()->published()->exists();
    }

    /**
     * @param  Builder<Course>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', CourseStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published
            && ($this->published_at === null || $this->published_at->isPast());
    }
}
