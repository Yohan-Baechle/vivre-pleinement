<?php

namespace App\Models;

use App\Enums\CourseStatus;
use App\Models\Concerns\HasOptimizedMedia;
use App\Models\Concerns\HasPriceInCents;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
