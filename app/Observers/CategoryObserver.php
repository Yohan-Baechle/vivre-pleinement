<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\InternalLinking;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->flushCaches($category);
    }

    public function deleted(Category $category): void
    {
        $this->flushCaches($category);
    }

    private function flushCaches(Category $category): void
    {
        Cache::forget('sitemap.urls');

        InternalLinking::flushCategory($category);
    }
}
