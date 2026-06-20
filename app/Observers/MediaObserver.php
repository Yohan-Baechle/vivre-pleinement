<?php

namespace App\Observers;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function created(Media $media): void
    {
        $this->storeDimensions($media);
    }

    private function storeDimensions(Media $media): void
    {
        if (! str_starts_with($media->mime_type ?? '', 'image/')) {
            return;
        }

        if ($media->getCustomProperty('width') && $media->getCustomProperty('height')) {
            return;
        }

        $path = $media->getPath();
        if (! is_file($path)) {
            return;
        }

        $size = @getimagesize($path);
        if ($size === false) {
            return;
        }

        $media->setCustomProperty('width', $size[0]);
        $media->setCustomProperty('height', $size[1]);
        $media->saveQuietly();
    }
}
