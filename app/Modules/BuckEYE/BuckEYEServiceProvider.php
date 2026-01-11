<?php

namespace App\Modules\BuckEYE;

use App\Modules\BuckEYE\Livewire\BuckEyeGame;
use App\Modules\BuckEYE\Livewire\BuckEyeUserStats;
use App\Modules\BuckEYE\Services\PuzzleService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BuckEYEServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PuzzleService::class, function ($app) {
            return new PuzzleService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Register Livewire components
        Livewire::component('buck-eye-game', BuckEyeGame::class);
        Livewire::component('buck-eye-user-stats', BuckEyeUserStats::class);
    }
}
