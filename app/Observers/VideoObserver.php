<?php

namespace App\Observers;

use App\Models\Video;
use Illuminate\Support\Facades\Cache;

class VideoObserver
{
    public function saved(Video $video): void
    {
        $this->flushCaches();
    }

    public function deleted(Video $video): void
    {
        $this->flushCaches();
    }

    public function restored(Video $video): void
    {
        $this->flushCaches();
    }

    public function forceDeleted(Video $video): void
    {
        $this->flushCaches();
    }

    private function flushCaches(): void
    {
        Cache::forget('sitemap.urls');
        Cache::forget('sitemap.videos');
    }
}
