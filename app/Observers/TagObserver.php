<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class TagObserver
{
    public function saved(Tag $tag): void
    {
        $this->flushCaches();
    }

    public function deleted(Tag $tag): void
    {
        $this->flushCaches();
    }

    private function flushCaches(): void
    {
        Cache::forget('sitemap.urls');
    }
}
