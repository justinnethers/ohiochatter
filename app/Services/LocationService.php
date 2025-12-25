<?php

namespace App\Services;

use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    /**
     * Validate the geographic hierarchy is correct
     */
    public function validateHierarchy(Region $region, County $county = null, City $city = null): void
    {
        if ($county && $county->region_id !== $region->id) {
            abort(404, 'County does not belong to this region');
        }
        
        if ($city && (!$county || $city->county_id !== $county->id)) {
            abort(404, 'City does not belong to this county');
        }
    }

    /**
     * Get all regions with their latest content
     */
    public function getAllRegionsWithContent(): Collection
    {
        return Cache::remember('regions_with_content', 3600, function () {
            return Region::with(['content' => function ($query) {
                $query->published()->latest('published_at')->take(3);
            }])->get();
        });
    }

    /**
     * Get comprehensive data for a region
     */
    public function getRegionData(Region $region): array
    {
        $cacheKey = "region_data_{$region->id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($region) {
            return [
                'counties' => $region->counties()
                    ->withCount('content')
                    ->orderBy('name')
                    ->get(),
                'featuredContent' => $this->getFeaturedContent(Region::class, $region->id),
                'childContent' => $this->getCountyContentInRegion($region),
                'categories' => $this->getCategoriesForLocation(Region::class, $region->id)
            ];
        });
    }

    /**
     * Get comprehensive data for a county
     */
    public function getCountyData(County $county): array
    {
        $cacheKey = "county_data_{$county->id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($county) {
            return [
                'cities' => $county->cities()
                    ->withCount('content')
                    ->orderBy('name')
                    ->get(),
                'featuredContent' => $this->getFeaturedContent(County::class, $county->id),
                'childContent' => $this->getCityContentInCounty($county),
                'categories' => $this->getCategoriesForLocation(County::class, $county->id)
            ];
        });
    }

    /**
     * Get comprehensive data for a city
     */
    public function getCityData(City $city): array
    {
        $cacheKey = "city_data_{$city->id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($city) {
            return [
                'featuredContent' => $this->getFeaturedContent(City::class, $city->id),
                'categories' => $this->getCategoriesForLocation(City::class, $city->id)
            ];
        });
    }

    /**
     * Get all content for a location (paginated)
     */
    public function getAllLocationContent(string $locatableType, int $locatableId, int $perPage = 12)
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get content for a specific category in a location
     */
    public function getLocationCategoryContent(string $locatableType, int $locatableId, int $categoryId, int $perPage = 12)
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->where('content_category_id', $categoryId)
            ->with(['contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get featured content for a location
     */
    private function getFeaturedContent(string $locatableType, int $locatableId, int $limit = 6): Collection
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->with(['contentCategory', 'contentType', 'author'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get content from counties in a region
     */
    private function getCountyContentInRegion(Region $region, int $limit = 6): Collection
    {
        // Use a subquery to avoid N+1: get county IDs directly in the query
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

    /**
     * Get content from cities in a county
     */
    private function getCityContentInCounty(County $county, int $limit = 6): Collection
    {
        // Use a subquery to avoid N+1: get city IDs directly in the query
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

    /**
     * Get categories for a location
     */
    public function getCategoriesForLocation(string $locatableType, int $locatableId): Collection
    {
        return ContentCategory::whereHas('content', function ($query) use ($locatableType, $locatableId) {
            $query->where('locatable_type', $locatableType)
                ->where('locatable_id', $locatableId)
                ->published();
        })->get();
    }

    /**
     * Clear cache for a location
     */
    public function clearLocationCache(string $type, int $id): void
    {
        $cacheKey = strtolower($type) . "_data_{$id}";
        Cache::forget($cacheKey);
        
        // Also clear parent caches if applicable
        if ($type === City::class) {
            $city = City::find($id);
            if ($city) {
                $this->clearLocationCache(County::class, $city->county_id);
            }
        }
        
        if ($type === County::class) {
            $county = County::find($id);
            if ($county) {
                $this->clearLocationCache(Region::class, $county->region_id);
            }
        }
        
        // Clear regions overview cache
        Cache::forget('regions_with_content');
    }
}