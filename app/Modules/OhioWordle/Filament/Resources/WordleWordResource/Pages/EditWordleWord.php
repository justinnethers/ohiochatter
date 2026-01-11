<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordleWordResource\Pages;

use App\Modules\OhioWordle\Filament\Resources\WordleWordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWordleWord extends EditRecord
{
    protected static string $resource = WordleWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
