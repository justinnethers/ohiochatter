<?php

namespace App\Filament\Widgets;

use App\Models\Puzzle;
use App\Models\UserGameProgress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TodaysPuzzleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todaysPuzzle = Puzzle::where('publish_date', Carbon::today()->toDateString())->first();

        if (!$todaysPuzzle) {
            return [
                Stat::make('Today\'s Puzzle', 'Not Available')
                    ->description('No puzzle is scheduled for today')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
                Stat::make('Players', '0'),
                Stat::make('Completion Rate', '0%'),
            ];
        }

        // Get total players
        $totalPlayers = UserGameProgress::where('puzzle_id', $todaysPuzzle->id)->count();

        // Get solved count
        $solvedCount = UserGameProgress::where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->count();

        // Calculate completion rate
        $completionRate = $totalPlayers > 0
            ? round(($solvedCount / $totalPlayers) * 100)
            : 0;

        // Get average guesses for solved puzzles
        $averageGuesses = UserGameProgress::where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $averageGuesses = $averageGuesses ? round($averageGuesses, 1) : 'N/A';

        // Get distribution of guesses
        $guessDistribution = UserGameProgress::where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->orderBy('guesses_taken')
            ->pluck('count', 'guesses_taken')
            ->toArray();

        // Format distribution for display
        $distributionText = empty($guessDistribution)
            ? 'No solved puzzles yet'
            : collect($guessDistribution)->map(function ($count, $guesses) {
                return "$guesses: $count";
            })->join(' | ');

        return [
            Stat::make('Today\'s Puzzle', $todaysPuzzle->answer)
                ->description('Category: ' . $todaysPuzzle->category)
                ->color('success'),

            Stat::make('Total Players', (string)$totalPlayers)
                ->description($totalPlayers === 1 ? '1 player has attempted' : "$totalPlayers players have attempted")
                ->color('primary'),

            Stat::make('Completion Rate', "$completionRate%")
                ->description("$solvedCount solved out of $totalPlayers")
                ->color($completionRate > 50 ? 'success' : 'warning'),

            Stat::make('Average Guesses', (string)$averageGuesses)
                ->description('For solved puzzles only')
                ->color('info'),

            Stat::make('Guess Distribution', '')
                ->description($distributionText)
                ->color('secondary'),
        ];
    }
}
