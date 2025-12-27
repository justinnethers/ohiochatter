<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Modules\Geography\Services\LocationCacheService;

class FetchCityData
{
    public function __construct(
        private LocationCacheService $cache,
        private FetchFeaturedContent $fetchFeaturedContent,
        private FetchCategoriesForLocation $fetchCategories
    ) {}

    public function execute(City $city): array
    {
        $cacheKey = $this->cache->buildLocationKey(City::class, $city->id);

        return $this->cache->remember(
            $cacheKey,
            fn() => $this->buildCityData($city)
        );
    }

    private function buildCityData(City $city): array
    {
        return [
            'featuredContent' => $this->fetchFeaturedContent->execute(City::class, $city->id),
            'categories' => $this->fetchCategories->execute(City::class, $city->id),
        ];
    }
}
