<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\Content;
use App\Modules\Geography\Services\LocationCacheService;

class DeleteContent
{
    public function __construct(
        private LocationCacheService $cacheService
    ) {}

    public function execute(Content $content): bool
    {
        $locatableType = $content->locatable_type;
        $locatableId = $content->locatable_id;

        $deleted = $content->delete();

        if ($deleted && $locatableType && $locatableId) {
            $this->cacheService->clearForLocation($locatableType, $locatableId);
        }

        return $deleted;
    }
}
