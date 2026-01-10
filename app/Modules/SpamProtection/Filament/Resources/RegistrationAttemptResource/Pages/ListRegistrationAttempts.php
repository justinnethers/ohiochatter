<?php

namespace App\Modules\SpamProtection\Filament\Resources\RegistrationAttemptResource\Pages;

use App\Modules\SpamProtection\Filament\Resources\RegistrationAttemptResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationAttempts extends ListRecords
{
    protected static string $resource = RegistrationAttemptResource::class;
}
