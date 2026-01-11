<?php

namespace App\Modules\OhioWordle\Filament\Widgets;

use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordleService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TodaysWordleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $word = WordleWord::where('publish_date', Carbon::today()->toDateString())->first();

        if (! $word) {
            return [
                Stat::make('OhioWordle', 'No puzzle today')
                    ->description('No word scheduled for today')
                    ->color('warning'),
            ];
        }

        $wordleService = app(WordleService::class);
        $stats = $wordleService->loadWordStats($word);

        return [
            Stat::make("Today's Word", $word->word)
                ->description($word->category ?? 'OhioWordle')
                ->color('success'),
            Stat::make('Players', $stats['totalPlayers'])
                ->description($stats['solvedCount'].' solved')
                ->color('info'),
            Stat::make('Completion Rate', $stats['completionRate'].'%')
                ->description('Avg: '.($stats['averageGuesses'] === 'N/A' ? 'N/A' : $stats['averageGuesses'].' guesses'))
                ->color($stats['completionRate'] >= 50 ? 'success' : 'warning'),
        ];
    }
}
