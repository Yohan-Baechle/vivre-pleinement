<?php

namespace App\Providers;

use App\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Media::observe(MediaObserver::class);

        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
