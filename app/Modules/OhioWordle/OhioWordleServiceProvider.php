<?php

namespace App\Modules\OhioWordle;

use App\Modules\OhioWordle\Services\DictionaryService;
use App\Modules\OhioWordle\Services\WordleService;
use Illuminate\Support\ServiceProvider;

class OhioWordleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DictionaryService::class, function ($app) {
            return new DictionaryService();
        });

        $this->app->singleton(WordleService::class, function ($app) {
            return new WordleService($app->make(DictionaryService::class));
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Register Livewire components when they're created
        // Livewire::component('ohio-wordle-game', OhioWordleGame::class);
        // Livewire::component('ohio-wordle-user-stats', OhioWordleUserStats::class);
    }
}
