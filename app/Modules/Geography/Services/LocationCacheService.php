<?php

namespace App\Modules\Geography\Services;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use Illuminate\Support\Facades\Cache;

class LocationCacheService
{
    private const DEFAULT_TTL = 1800; // 30 minutes
    private const REGIONS_OVERVIEW_TTL = 3600; // 1 hour

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? self::DEFAULT_TTL, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function buildLocationKey(string $locationType, int $locationId): string
    {
        $prefix = $this->getLocationPrefix($locationType);

        return "{$prefix}_data_{$locationId}";
    }

    public function clearForLocation(string $locationType, int $locationId): void
    {
        $key = $this->buildLocationKey($locationType, $locationId);
        $this->forget($key);

        $this->clearParentCaches($locationType, $locationId);

        $this->forget('regions_with_content');
    }

    public function clearRegionsOverview(): void
    {
        $this->forget('regions_with_content');
    }

    public function getDefaultTtl(): int
    {
        return self::DEFAULT_TTL;
    }

    public function getRegionsOverviewTtl(): int
    {
        return self::REGIONS_OVERVIEW_TTL;
    }

    private function clearParentCaches(string $locationType, int $locationId): void
    {
        if ($locationType === City::class) {
            $city = City::find($locationId);
            if ($city) {
                $this->clearForLocation(County::class, $city->county_id);
            }
        }

        if ($locationType === County::class) {
            $county = County::find($locationId);
            if ($county) {
                $this->clearForLocation(Region::class, $county->region_id);
            }
        }
    }

    private function getLocationPrefix(string $locationType): string
    {
        return match ($locationType) {
            Region::class => 'region',
            County::class => 'county',
            City::class => 'city',
            default => 'location',
        };
    }
}
