<?php

namespace App\Modules\BuckEYE\Filament\Resources\PuzzleResource\Pages;

use App\Modules\BuckEYE\Filament\Resources\PuzzleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPuzzle extends ViewRecord
{
    protected static string $resource = PuzzleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
