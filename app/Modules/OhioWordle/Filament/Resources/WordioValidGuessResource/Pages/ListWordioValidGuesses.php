<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordioValidGuessResource\Pages;

use App\Modules\OhioWordle\Filament\Resources\WordioValidGuessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWordioValidGuesses extends ListRecords
{
    protected static string $resource = WordioValidGuessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
