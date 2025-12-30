<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\Content;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\Services\LocationCacheService;
use Illuminate\Database\Eloquent\Collection;

class FetchRegionData
{
    public function __construct(
        private LocationCacheService $cache,
        private FetchFeaturedContent $fetchFeaturedContent,
        private FetchCategoriesForLocation $fetchCategories
    ) {}

    public function execute(Region $region): array
    {
        $cacheKey = $this->cache->buildLocationKey(Region::class, $region->id);

        return $this->cache->remember(
            $cacheKey,
            fn() => $this->buildRegionData($region)
        );
    }

    private function buildRegionData(Region $region): array
    {
        return [
            'counties' => $this->getCountiesWithHierarchicalContentCount($region),
            'featuredContent' => $this->fetchFeaturedContent->execute(Region::class, $region->id),
            'childContent' => $this->getChildContentInRegion($region),
            'categories' => $this->fetchCategories->execute(Region::class, $region->id),
            'totalContentCount' => $this->getTotalContentCount($region),
        ];
    }

    /**
     * Get counties with content count that includes city content.
     */
    private function getCountiesWithHierarchicalContentCount(Region $region): Collection
    {
        $counties = $region->counties()->orderBy('name')->get();

        // Calculate hierarchical content count for each county
        foreach ($counties as $county) {
            $county->content_count = $this->getCountyTotalContentCount($county);
        }

        return $counties;
    }

    /**
     * Get total content count for a county including its cities.
     */
    private function getCountyTotalContentCount(County $county): int
    {
        return Content::where(function ($q) use ($county) {
            // Direct county content
            $q->where(function ($sub) use ($county) {
                $sub->where('locatable_type', County::class)
                    ->where('locatable_id', $county->id);
            })
            // City content within county
            ->orWhere(function ($sub) use ($county) {
                $sub->where('locatable_type', City::class)
                    ->whereIn('locatable_id', function ($cityQuery) use ($county) {
                        $cityQuery->select('id')
                            ->from('cities')
                            ->where('county_id', $county->id);
                    });
            });
        })->published()->count();
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

    /**
     * Get recent content from child locations (counties and cities).
     */
    private function getChildContentInRegion(Region $region, int $limit = 6): Collection
    {
        return Content::where(function ($q) use ($region) {
            // County content within region
            $q->where(function ($sub) use ($region) {
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
        })
            ->with(['contentCategory', 'contentType', 'locatable', 'author'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
