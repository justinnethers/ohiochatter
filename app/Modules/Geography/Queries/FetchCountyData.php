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
            'cities' => $county->cities()
                ->withCount('content')
                ->orderBy('name')
                ->get(),
            'featuredContent' => $this->fetchFeaturedContent->execute(County::class, $county->id),
            'childContent' => $this->getCityContentInCounty($county),
            'categories' => $this->fetchCategories->execute(County::class, $county->id),
        ];
    }

    private function getCityContentInCounty(County $county, int $limit = 6): Collection
    {
        return Content::where('locatable_type', City::class)
            ->whereIn('locatable_id', function ($query) use ($county) {
                $query->select('id')
                    ->from('cities')
                    ->where('county_id', $county->id);
            })
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
