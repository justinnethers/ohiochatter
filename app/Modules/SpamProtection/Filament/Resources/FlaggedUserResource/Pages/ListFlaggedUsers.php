<?php

namespace App\Modules\SpamProtection\Filament\Resources\FlaggedUserResource\Pages;

use App\Modules\SpamProtection\Filament\Resources\FlaggedUserResource;
use Filament\Resources\Pages\ListRecords;

class ListFlaggedUsers extends ListRecords
{
    protected static string $resource = FlaggedUserResource::class;
}
