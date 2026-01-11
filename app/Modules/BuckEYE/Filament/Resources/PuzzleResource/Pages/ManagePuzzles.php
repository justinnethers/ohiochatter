<?php

namespace App\Modules\BuckEYE\Filament\Resources\PuzzleResource\Pages;

use App\Modules\BuckEYE\Filament\Resources\PuzzleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePuzzles extends ManageRecords
{
    protected static string $resource = PuzzleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
