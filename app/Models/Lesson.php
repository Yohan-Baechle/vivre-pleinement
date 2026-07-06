<?php

namespace App\Models;

use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'module_id',
    'title',
    'slug',
    'content',
    'video_provider',
    'video_id',
    'duration_seconds',
    'position',
    'is_free_preview',
])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'position' => 0,
        'is_free_preview' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_free_preview' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Récupère la formation parente via le module.
     */
    public function course(): ?Course
    {
        return $this->module?->course;
    }

    public function embedUrl(): ?string
    {
        if ($this->video_id === null) {
            return null;
        }

        return match ($this->video_provider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$this->video_id,
            'vimeo' => 'https://player.vimeo.com/video/'.$this->video_id,
            default => null,
        };
    }
}
