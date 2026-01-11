<?php

namespace App\Modules\BuckEYE\Filament\Resources\PuzzleResource\Pages;

use App\Modules\BuckEYE\Filament\Resources\PuzzleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPuzzle extends EditRecord
{
    protected static string $resource = PuzzleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
