<?php

namespace App\Observers;

use App\Models\Course;
use App\Support\IndexNow;
use Illuminate\Support\Facades\Cache;

class CourseObserver
{
    public function saved(Course $course): void
    {
        $this->flushCaches();

        if ($course->isPublished()) {
            IndexNow::ping(route('courses.show', $course));
        }
    }

    public function deleted(Course $course): void
    {
        $this->flushCaches();
    }

    public function restored(Course $course): void
    {
        $this->flushCaches();
    }

    public function forceDeleted(Course $course): void
    {
        $this->flushCaches();
    }

    private function flushCaches(): void
    {
        Cache::forget('sitemap.urls');
    }
}
