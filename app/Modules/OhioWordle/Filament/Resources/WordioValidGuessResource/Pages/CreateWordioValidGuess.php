<?php

namespace App\Modules\OhioWordle\Filament\Resources\WordioValidGuessResource\Pages;

use App\Modules\OhioWordle\Filament\Resources\WordioValidGuessResource;
use App\Modules\OhioWordle\Services\DictionaryService;
use Filament\Resources\Pages\CreateRecord;

class CreateWordioValidGuess extends CreateRecord
{
    protected static string $resource = WordioValidGuessResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approved_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(DictionaryService::class)->clearCache();
    }
}
