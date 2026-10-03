<?php

namespace App\Console;

use App\Console\Commands\ProcessThreadsForSeo;
use App\Modules\OhioWordle\Commands\CreateDailyPuzzle;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ProcessThreadsForSeo::class)
            ->dailyAt('03:00')
            ->timezone('America/New_York')
            ->when(fn (): bool => (bool) config('services.openai.seo_meta_generation_enabled'));

        $schedule->command(CreateDailyPuzzle::class)
            ->dailyAt('00:00')
            ->timezone('America/New_York')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/wordle-scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
