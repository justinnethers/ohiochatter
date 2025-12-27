<?php

namespace App\Modules\Geography\Queries;

use App\Models\Region;
use App\Modules\Geography\Services\LocationCacheService;
use Illuminate\Database\Eloquent\Collection;

class FetchRegionsWithContent
{
    public function __construct(
        private LocationCacheService $cache
    ) {}

    public function execute(): Collection
    {
        return $this->cache->remember(
            'regions_with_content',
            fn() => $this->fetchRegions(),
            $this->cache->getRegionsOverviewTtl()
        );
    }

    private function fetchRegions(): Collection
    {
        return Region::with(['content' => function ($query) {
            $query->published()->latest('published_at')->take(3);
        }])->get();
    }
}
