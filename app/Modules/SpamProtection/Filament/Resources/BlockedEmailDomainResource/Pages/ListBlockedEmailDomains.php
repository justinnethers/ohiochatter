<?php

namespace App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource\Pages;

use App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlockedEmailDomains extends ListRecords
{
    protected static string $resource = BlockedEmailDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
