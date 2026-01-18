<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource\Widgets;

use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TopRejectedGuessesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $topGuesses = WordioRejectedGuess::select('guess', DB::raw('COUNT(*) as count'))
            ->where('reason', WordioRejectedGuess::REASON_NOT_IN_DICTIONARY)
            ->groupBy('guess')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $todayCount = WordioRejectedGuess::whereDate('created_at', today())->count();
        $weekCount = WordioRejectedGuess::where('created_at', '>=', now()->subWeek())->count();
        $totalCount = WordioRejectedGuess::count();

        $stats = [
            Stat::make('Today', $todayCount)
                ->description('Rejected guesses today')
                ->color('warning'),
            Stat::make('This Week', $weekCount)
                ->description('Rejected guesses this week')
                ->color('warning'),
            Stat::make('Total', $totalCount)
                ->description('All time rejected guesses')
                ->color('danger'),
        ];

        if ($topGuesses->isNotEmpty()) {
            $topList = $topGuesses->map(fn ($g) => "{$g->guess} ({$g->count})")->join(', ');
            $stats[] = Stat::make('Top Invalid Words', $topGuesses->first()->guess)
                ->description($topList)
                ->color('info');
        }

        return $stats;
    }
}
