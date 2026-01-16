<?php

namespace App\Modules\OhioWordle;

use App\Modules\OhioWordle\Commands\CreateDailyPuzzle;
use App\Modules\OhioWordle\Livewire\OhioWordleGame;
use App\Modules\OhioWordle\Livewire\OhioWordleUserStats;
use App\Modules\OhioWordle\Services\DictionaryService;
use App\Modules\OhioWordle\Services\WordleService;
use App\Modules\OhioWordle\Services\WordRotationService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        $this->app->singleton(WordRotationService::class, function ($app) {
            return new WordRotationService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        Livewire::component('ohio-wordle-game', OhioWordleGame::class);
        Livewire::component('ohio-wordle-user-stats', OhioWordleUserStats::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateDailyPuzzle::class,
            ]);
        }
    }
}
