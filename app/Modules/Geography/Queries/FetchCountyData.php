<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\Content;
use App\Models\County;
use App\Modules\Geography\Services\LocationCacheService;
use Illuminate\Database\Eloquent\Collection;

class FetchCountyData
{
    public function __construct(
        private LocationCacheService $cache,
        private FetchFeaturedContent $fetchFeaturedContent,
        private FetchCategoriesForLocation $fetchCategories
    ) {}

    public function execute(County $county): array
    {
        $cacheKey = $this->cache->buildLocationKey(County::class, $county->id);

        return $this->cache->remember(
            $cacheKey,
            fn() => $this->buildCountyData($county)
        );
    }

    private function buildCountyData(County $county): array
    {
        return [
            'cities' => $this->getCitiesWithContentCount($county),
            'featuredContent' => $this->fetchFeaturedContent->execute(County::class, $county->id),
            'childContent' => $this->getCityContentInCounty($county),
            'categories' => $this->fetchCategories->execute(County::class, $county->id),
            'totalContentCount' => $this->getTotalContentCount($county),
        ];
    }

    /**
     * Get cities with their direct content count.
     */
    private function getCitiesWithContentCount(County $county): Collection
    {
        return $county->cities()
            ->withCount('content')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get total content count for county including all cities.
     */
    private function getTotalContentCount(County $county): int
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
     * Get recent content from the county and all cities within it.
     */
    private function getCityContentInCounty(County $county, int $limit = 6): Collection
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
        })
            ->with(['contentCategory', 'contentType', 'locatable', 'author'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
