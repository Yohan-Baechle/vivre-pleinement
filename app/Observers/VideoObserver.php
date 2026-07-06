<?php

namespace App\Observers;

use App\Models\Video;
use App\Support\IndexNow;
use App\Support\VideoArticleMatcher;
use Illuminate\Support\Facades\Cache;

class VideoObserver
{
    public function saved(Video $video): void
    {
        $this->flushCaches();

        if (Video::query()->indexable()->whereKey($video->getKey())->exists()) {
            IndexNow::ping(route('videos.show', $video->slug));
        }
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

        VideoArticleMatcher::flush();
    }
}
