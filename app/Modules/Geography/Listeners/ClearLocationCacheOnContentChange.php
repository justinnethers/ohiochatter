<?php

namespace App\Modules\Geography\Listeners;

use App\Modules\Geography\Events\ContentCreated;
use App\Modules\Geography\Events\ContentFeatured;
use App\Modules\Geography\Events\ContentPublished;
use App\Modules\Geography\Services\LocationCacheService;

class ClearLocationCacheOnContentChange
{
    public function __construct(
        private LocationCacheService $cacheService
    ) {}

    public function handle(ContentCreated|ContentPublished|ContentFeatured $event): void
    {
        $content = $event->content;

        if ($content->locatable_type && $content->locatable_id) {
            $this->cacheService->clearForLocation(
                $content->locatable_type,
                $content->locatable_id
            );
        }

        $this->cacheService->clearRegionsOverview();
    }
}
