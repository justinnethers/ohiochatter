<?php

namespace App\Traits;

use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Services\LocationService;

trait HandlesLocationContent
{
    /**
     * Get the location service instance
     */
    protected function getLocationService(): LocationService
    {
        return app(LocationService::class);
    }

    /**
     * Validate and get location hierarchy data
     */
    protected function validateAndGetLocationData(?Region $region = null, ?County $county = null, ?City $city = null): array
    {
        $locationService = $this->getLocationService();
        
        // Validate hierarchy if multiple levels provided
        if ($region && ($county || $city)) {
            $locationService->validateHierarchy($region, $county, $city);
        }

        // Determine the most specific location
        $location = $city ?? $county ?? $region;
        $locationType = match (true) {
            $city !== null => City::class,
            $county !== null => County::class,
            $region !== null => Region::class,
            default => throw new \InvalidArgumentException('At least one location must be provided')
        };

        return [
            'location' => $location,
            'locationType' => $locationType,
            'region' => $region,
            'county' => $county,
            'city' => $city
        ];
    }

    /**
     * Get comprehensive content data for a location
     */
    protected function getLocationContentData(string $locationType, int $locationId): array
    {
        $locationService = $this->getLocationService();
        
        return match ($locationType) {
            Region::class => $locationService->getRegionData(Region::find($locationId)),
            County::class => $locationService->getCountyData(County::find($locationId)),
            City::class => $locationService->getCityData(City::find($locationId)),
            default => throw new \InvalidArgumentException("Unsupported location type: {$locationType}")
        };
    }

    /**
     * Get breadcrumb data for the location hierarchy
     */
    protected function getBreadcrumbs(?Region $region = null, ?County $county = null, ?City $city = null): array
    {
        $breadcrumbs = [];

        if ($region) {
            $breadcrumbs['region'] = [
                'name' => $region->name,
                'url' => route('region.show', $region->slug)
            ];
        }

        if ($county) {
            $breadcrumbs['county'] = [
                'name' => $county->name,
                'url' => route('county.show', [$region->slug, $county->slug])
            ];
        }

        if ($city) {
            $breadcrumbs['city'] = [
                'name' => $city->name,
                'url' => route('city.show', [$region->slug, $county->slug, $city->slug])
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Generate SEO data for location-based content
     */
    protected function generateLocationSeoData(?Region $region = null, ?County $county = null, ?City $city = null, ?string $category = null): array
    {
        $location = $city ?? $county ?? $region;
        $locationName = $location->name;
        
        $title = $locationName . ' Guide';
        $description = "Discover the best of {$locationName}, Ohio";
        
        if ($category) {
            $title = "{$category} in {$locationName}";
            $description = "Find the best {$category} in {$locationName}, Ohio";
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical_url' => request()->url()
        ];
    }
}