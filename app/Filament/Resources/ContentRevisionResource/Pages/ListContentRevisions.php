<?php

namespace App\Filament\Resources\ContentRevisionResource\Pages;

use App\Filament\Resources\ContentRevisionResource;
use Filament\Resources\Pages\ListRecords;

class ListContentRevisions extends ListRecords
{
    protected static string $resource = ContentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
