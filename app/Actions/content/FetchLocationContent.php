<?php

namespace App\Actions\Content;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Models\Content;
use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FetchLocationContent
{
    /**
     * Get content for a region page
     *
     * @param Region $region
     * @param int $featureLimit
     * @param int $childContentLimit
     * @return array
     */
    public function forRegion(Region $region, int $featureLimit = 6, int $childContentLimit = 6): array
    {
        return [
            'featuredContent' => $this->getFeaturedContent(Region::class, $region->id, $featureLimit),
            'childContent' => $this->getCountyContentInRegion($region, $childContentLimit),
            'categories' => $this->getCategoriesForLocation(Region::class, $region->id),
        ];
    }

    /**
     * Get all paginated content for a region
     *
     * @param Region $region
     * @param int $perPage
     * @return array
     */
    public function allContentForRegion(Region $region, int $perPage = 12): array
    {
        return [
            'content' => $this->getPaginatedContent(Region::class, $region->id, $perPage),
            'categories' => $this->getCategoriesForLocation(Region::class, $region->id),
        ];
    }

    /**
     * Get content for a county page
     *
     * @param County $county
     * @param int $featureLimit
     * @param int $childContentLimit
     * @return array
     */
    public function forCounty(County $county, int $featureLimit = 6, int $childContentLimit = 6): array
    {
        return [
            'featuredContent' => $this->getFeaturedContent(County::class, $county->id, $featureLimit),
            'childContent' => $this->getCityContentInCounty($county, $childContentLimit),
            'categories' => $this->getCategoriesForLocation(County::class, $county->id),
        ];
    }

    /**
     * Get all paginated content for a county
     *
     * @param County $county
     * @param int $perPage
     * @return array
     */
    public function allContentForCounty(County $county, int $perPage = 12): array
    {
        return [
            'content' => $this->getPaginatedContent(County::class, $county->id, $perPage),
            'categories' => $this->getCategoriesForLocation(County::class, $county->id),
        ];
    }

    /**
     * Get content for a city page
     *
     * @param City $city
     * @param int $limit
     * @return array
     */
    public function forCity(City $city, int $limit = 6): array
    {
        return [
            'featuredContent' => $this->getFeaturedContent(City::class, $city->id, $limit),
            'categories' => $this->getCategoriesForLocation(City::class, $city->id),
        ];
    }

    /**
     * Get all paginated content for a city
     *
     * @param City $city
     * @param int $perPage
     * @return array
     */
    public function allContentForCity(City $city, int $perPage = 12): array
    {
        return [
            'content' => $this->getPaginatedContent(City::class, $city->id, $perPage),
            'categories' => $this->getCategoriesForLocation(City::class, $city->id),
        ];
    }

    /**
     * Get content for a specific category in a location
     *
     * @param string $locatableType
     * @param int $locatableId
     * @param int $categoryId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function contentForLocationCategory(string $locatableType, int $locatableId, int $categoryId, int $perPage = 12): LengthAwarePaginator
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
     *
     * @param string $locatableType
     * @param int $locatableId
     * @param int $limit
     * @return Collection
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
     * Get paginated content for a location
     *
     * @param string $locatableType
     * @param int $locatableId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    private function getPaginatedContent(string $locatableType, int $locatableId, int $perPage = 12): LengthAwarePaginator
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get content from counties in a region
     *
     * @param Region $region
     * @param int $limit
     * @return Collection
     */
    private function getCountyContentInRegion(Region $region, int $limit = 6): Collection
    {
        $countyIds = $region->counties->pluck('id');

        return Content::where('locatable_type', County::class)
            ->whereIn('locatable_id', $countyIds)
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get content from cities in a county
     *
     * @param County $county
     * @param int $limit
     * @return Collection
     */
    private function getCityContentInCounty(County $county, int $limit = 6): Collection
    {
        $cityIds = $county->cities->pluck('id');

        return Content::where('locatable_type', City::class)
            ->whereIn('locatable_id', $cityIds)
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get categories for a location
     *
     * @param string $locatableType
     * @param int $locatableId
     * @return Collection
     */
    private function getCategoriesForLocation(string $locatableType, int $locatableId): Collection
    {
        return ContentCategory::whereHas('content', function ($query) use ($locatableType, $locatableId) {
            $query->where('locatable_type', $locatableType)
                ->where('locatable_id', $locatableId)
                ->published();
        })->get();
    }
}
