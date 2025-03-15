<?php

namespace App\Filament\Widgets;

use App\Models\AnonymousGameProgress;
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
        $todaysPuzzle = Puzzle::query()
            ->where('publish_date', Carbon::today()->toDateString())
            ->first();

        if (!$todaysPuzzle) {
            return [
                Stat::make("Today's Puzzle", 'Not Available')
                    ->description('No puzzle is scheduled for today')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
                Stat::make('Players', '0'),
                Stat::make('Completion Rate', '0%'),
            ];
        }

        $totalRegPlayers = UserGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('user_id', '!=', 1)
            ->count();

        $totalAnonPlayers = AnonymousGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->count();

        $totalPlayers = $totalRegPlayers + $totalAnonPlayers;

        $solvedRegCount = UserGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->where('user_id', '!=', 1)
            ->count();

        $solvedAnonCount = AnonymousGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->count();

        $solvedCount = $solvedRegCount + $solvedAnonCount;

        $completionRate = $totalPlayers > 0
            ? round(($solvedCount / $totalPlayers) * 100)
            : 0;

        $avgRegGuesses = UserGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->where('user_id', '!=', 1)
            ->avg('guesses_taken');

        $avgAnonGuesses = AnonymousGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $averageGuesses = 'N/A';
        if ($solvedRegCount > 0 || $solvedAnonCount > 0) {
            $totalGuesses = 0;
            $totalSolved = 0;

            if ($solvedRegCount > 0 && $avgRegGuesses) {
                $totalGuesses += $avgRegGuesses * $solvedRegCount;
                $totalSolved += $solvedRegCount;
            }

            if ($solvedAnonCount > 0 && $avgAnonGuesses) {
                $totalGuesses += $avgAnonGuesses * $solvedAnonCount;
                $totalSolved += $solvedAnonCount;
            }

            if ($totalSolved > 0) {
                $averageGuesses = round($totalGuesses / $totalSolved, 1);
            }
        }

        $regDistribution = UserGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->where('user_id', '!=', 1)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->orderBy('guesses_taken')
            ->pluck('count', 'guesses_taken')
            ->toArray();

        $anonDistribution = AnonymousGameProgress::query()
            ->where('puzzle_id', $todaysPuzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->orderBy('guesses_taken')
            ->pluck('count', 'guesses_taken')
            ->toArray();

        $guessDistribution = [];

        foreach (range(1, 5) as $guessNum) {
            $regCount = $regDistribution[$guessNum] ?? 0;
            $anonCount = $anonDistribution[$guessNum] ?? 0;
            $guessDistribution[$guessNum] = $regCount + $anonCount;
        }

        $distributionText = empty($guessDistribution) || array_sum($guessDistribution) === 0
            ? 'No solved puzzles yet'
            : collect($guessDistribution)->map(function ($count, $guesses) {
                return "$guesses: $count";
            })->join(' | ');

        return [
            Stat::make("Today's Puzzle", $todaysPuzzle->answer)
                ->description('Category: ' . $todaysPuzzle->category)
                ->color('success'),

            Stat::make('Total Players', (string)$totalPlayers)
                ->description("$totalRegPlayers registered / $totalAnonPlayers guests")
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
