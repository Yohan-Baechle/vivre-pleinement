<?php

namespace App\Providers;

use App\Observers\MediaObserver;
use App\Support\FontPreloads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Media::observe(MediaObserver::class);

        Model::shouldBeStrict(! $this->app->isProduction());

        Vite::usePreloadTagAttributes(function (string $src, string $url) {
            if ($src === 'fonts' && ! FontPreloads::shouldPreload($url)) {
                return false;
            }

            return [];
        });
    }
}
