<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource\Pages;

use App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource;
use App\Modules\OhioWordle\Filament\Resources\WordioRejectedGuessResource\Widgets\TopRejectedGuessesWidget;
use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWordioRejectedGuesses extends ListRecords
{
    protected static string $resource = WordioRejectedGuessResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TopRejectedGuessesWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'not_in_dictionary' => Tab::make('Not in Dictionary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('reason', WordioRejectedGuess::REASON_NOT_IN_DICTIONARY))
                ->badge(WordioRejectedGuess::where('reason', WordioRejectedGuess::REASON_NOT_IN_DICTIONARY)->count()),
            'wrong_length' => Tab::make('Wrong Length')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('reason', WordioRejectedGuess::REASON_WRONG_LENGTH))
                ->badge(WordioRejectedGuess::where('reason', WordioRejectedGuess::REASON_WRONG_LENGTH)->count()),
            'empty' => Tab::make('Empty')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('reason', WordioRejectedGuess::REASON_EMPTY))
                ->badge(WordioRejectedGuess::where('reason', WordioRejectedGuess::REASON_EMPTY)->count()),
        ];
    }
}
