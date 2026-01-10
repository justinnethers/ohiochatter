<?php

namespace App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource\Pages;

use App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlockedEmailDomain extends CreateRecord
{
    protected static string $resource = BlockedEmailDomainResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['added_by'] = auth()->id();
        $data['domain'] = strtolower($data['domain']);

        return $data;
    }
}
