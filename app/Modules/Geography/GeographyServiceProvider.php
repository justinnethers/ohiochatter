<?php

namespace App\Modules\Geography;

use App\Modules\Geography\Events\ContentCreated;
use App\Modules\Geography\Events\ContentFeatured;
use App\Modules\Geography\Events\ContentPublished;
use App\Modules\Geography\Listeners\ClearLocationCacheOnContentChange;
use App\Modules\Geography\Services\GeographySeoService;
use App\Modules\Geography\Services\LocationCacheService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GeographyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocationCacheService::class, function ($app) {
            return new LocationCacheService();
        });

        $this->app->singleton(GeographySeoService::class, function ($app) {
            return new GeographySeoService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->registerEventListeners();
    }

    protected function registerEventListeners(): void
    {
        Event::listen(ContentCreated::class, ClearLocationCacheOnContentChange::class);
        Event::listen(ContentPublished::class, ClearLocationCacheOnContentChange::class);
        Event::listen(ContentFeatured::class, ClearLocationCacheOnContentChange::class);
    }
}
