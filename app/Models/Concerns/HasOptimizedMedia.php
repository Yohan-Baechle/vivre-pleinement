<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasOptimizedMedia
{
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && ! str_starts_with($media->mime_type ?? '', 'image/')) {
            return;
        }

        $this->addConversion('thumb', 400);
        $this->addConversion('medium', 800);
        $this->addConversion('large', 1600);
    }

    private function addConversion(string $name, int $width): Conversion
    {
        return $this->addMediaConversion($name)
            ->format('webp')
            ->fit(Fit::Max, $width, $width * 2)
            ->quality(80)
            ->nonQueued();
    }
}
