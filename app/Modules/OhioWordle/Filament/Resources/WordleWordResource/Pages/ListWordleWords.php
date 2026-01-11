<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordleWordResource\Pages;

use App\Modules\OhioWordle\Filament\Resources\WordleWordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWordleWords extends ListRecords
{
    protected static string $resource = WordleWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
