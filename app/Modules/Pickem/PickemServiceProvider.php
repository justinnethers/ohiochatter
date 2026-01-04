<?php

namespace App\Modules\Pickem;

use App\Modules\Pickem\Services\PickemScoringService;
use Illuminate\Support\ServiceProvider;

class PickemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PickemScoringService::class, function ($app) {
            return new PickemScoringService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
