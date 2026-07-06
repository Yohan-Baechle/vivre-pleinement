<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\IndexNow;
use App\Support\InternalLinking;
use App\Support\VideoArticleMatcher;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function saving(Post $post): void
    {
        if ($post->isDirty('content')) {
            $post->reading_time_minutes = Post::computeReadingTimeMinutes((string) $post->content);
        }
    }

    public function saved(Post $post): void
    {
        $this->flushCaches($post);

        if ($post->status === PostStatus::Published && $post->published_at?->isPast()) {
            IndexNow::ping(route('blog.show', $post->slug));
        }
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
        VideoArticleMatcher::flush();
    }
}
