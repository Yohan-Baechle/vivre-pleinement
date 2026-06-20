<?php

namespace App\Observers;

use App\Models\Post;
use App\Support\InternalLinking;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function saved(Post $post): void
    {
        $this->flushCaches($post);
    }

    public function deleted(Post $post): void
    {
        $this->flushCaches($post);
    }

    public function restored(Post $post): void
    {
        $this->flushCaches($post);
    }

    public function forceDeleted(Post $post): void
    {
        $this->flushCaches($post);
    }

    private function flushCaches(Post $post): void
    {
        Cache::forget('sitemap.urls');
        Cache::forget('blog.rss.posts');

        InternalLinking::flushCluster($post);
    }
}
