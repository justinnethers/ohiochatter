<?php

namespace App\Modules\Geography\Queries;

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
            'counties' => $region->counties()
                ->withCount('content')
                ->orderBy('name')
                ->get(),
            'featuredContent' => $this->fetchFeaturedContent->execute(Region::class, $region->id),
            'childContent' => $this->getCountyContentInRegion($region),
            'categories' => $this->fetchCategories->execute(Region::class, $region->id),
        ];
    }

    private function getCountyContentInRegion(Region $region, int $limit = 6): Collection
    {
        return Content::where('locatable_type', County::class)
            ->whereIn('locatable_id', function ($query) use ($region) {
                $query->select('id')
                    ->from('counties')
                    ->where('region_id', $region->id);
            })
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
