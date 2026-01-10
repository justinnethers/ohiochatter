<?php

namespace App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource\Pages;

use App\Modules\SpamProtection\Filament\Resources\BlockedEmailDomainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlockedEmailDomain extends EditRecord
{
    protected static string $resource = BlockedEmailDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['domain'] = strtolower($data['domain']);

        return $data;
    }
}
