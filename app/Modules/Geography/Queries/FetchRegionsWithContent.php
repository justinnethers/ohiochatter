<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\Content;
use App\Models\County;
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
        $regions = Region::withCount('counties')->get();

        // Calculate hierarchical content count for each region
        foreach ($regions as $region) {
            $region->total_content_count = $this->getTotalContentCount($region);
        }

        return $regions;
    }

    /**
     * Get total content count for region including all counties and cities.
     */
    private function getTotalContentCount(Region $region): int
    {
        return Content::where(function ($q) use ($region) {
            // Direct region content
            $q->where(function ($sub) use ($region) {
                $sub->where('locatable_type', Region::class)
                    ->where('locatable_id', $region->id);
            })
            // County content within region
            ->orWhere(function ($sub) use ($region) {
                $sub->where('locatable_type', County::class)
                    ->whereIn('locatable_id', function ($countyQuery) use ($region) {
                        $countyQuery->select('id')
                            ->from('counties')
                            ->where('region_id', $region->id);
                    });
            })
            // City content within region
            ->orWhere(function ($sub) use ($region) {
                $sub->where('locatable_type', City::class)
                    ->whereIn('locatable_id', function ($cityQuery) use ($region) {
                        $cityQuery->select('cities.id')
                            ->from('cities')
                            ->join('counties', 'cities.county_id', '=', 'counties.id')
                            ->where('counties.region_id', $region->id);
                    });
            });
        })->published()->count();
    }
}
