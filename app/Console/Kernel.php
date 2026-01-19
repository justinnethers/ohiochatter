<?php

namespace App\Console;

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
        \Log::info('Scheduler running at ' . now());

//        $schedule->command(ProcessThreadsForSeo::class)->everyFiveMinutes();

        $schedule->command(CreateDailyPuzzle::class)
            ->everyFiveMinutes()
            ->appendOutputTo(storage_path('logs/wordle-scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
