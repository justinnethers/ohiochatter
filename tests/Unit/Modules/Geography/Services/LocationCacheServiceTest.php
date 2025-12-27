<?php

namespace Tests\Unit\Modules\Geography\Services;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\Services\LocationCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->cacheService = new LocationCacheService();
    Cache::flush();

    $this->region = Region::factory()->create();
    $this->county = County::factory()->create(['region_id' => $this->region->id]);
    $this->city = City::factory()->create(['county_id' => $this->county->id]);
});

test('it caches data with default TTL', function () {
    $result = $this->cacheService->remember('test_key', fn() => 'test_value');

    expect($result)->toBe('test_value')
        ->and(Cache::has('test_key'))->toBeTrue();
});

test('it returns cached data on subsequent calls', function () {
    $callCount = 0;
    $callback = function () use (&$callCount) {
        $callCount++;
        return 'value_' . $callCount;
    };

    $first = $this->cacheService->remember('test_key', $callback);
    $second = $this->cacheService->remember('test_key', $callback);

    expect($first)->toBe('value_1')
        ->and($second)->toBe('value_1')
        ->and($callCount)->toBe(1);
});

test('it forgets a specific cache key', function () {
    Cache::put('test_key', 'test_value', 3600);

    $this->cacheService->forget('test_key');

    expect(Cache::has('test_key'))->toBeFalse();
});

test('it clears cache for a city', function () {
    Cache::put("city_data_{$this->city->id}", 'city_data', 3600);

    $this->cacheService->clearForLocation(City::class, $this->city->id);

    expect(Cache::has("city_data_{$this->city->id}"))->toBeFalse();
});

test('it cascades cache clear from city to county', function () {
    Cache::put("city_data_{$this->city->id}", 'city_data', 3600);
    Cache::put("county_data_{$this->county->id}", 'county_data', 3600);

    $this->cacheService->clearForLocation(City::class, $this->city->id);

    expect(Cache::has("city_data_{$this->city->id}"))->toBeFalse()
        ->and(Cache::has("county_data_{$this->county->id}"))->toBeFalse();
});

test('it cascades cache clear from county to region', function () {
    Cache::put("county_data_{$this->county->id}", 'county_data', 3600);
    Cache::put("region_data_{$this->region->id}", 'region_data', 3600);

    $this->cacheService->clearForLocation(County::class, $this->county->id);

    expect(Cache::has("county_data_{$this->county->id}"))->toBeFalse()
        ->and(Cache::has("region_data_{$this->region->id}"))->toBeFalse();
});

test('it clears regions overview when clearing any location', function () {
    Cache::put('regions_with_content', 'overview_data', 3600);
    Cache::put("region_data_{$this->region->id}", 'region_data', 3600);

    $this->cacheService->clearForLocation(Region::class, $this->region->id);

    expect(Cache::has('regions_with_content'))->toBeFalse()
        ->and(Cache::has("region_data_{$this->region->id}"))->toBeFalse();
});

test('it builds correct cache key for region', function () {
    $key = $this->cacheService->buildLocationKey(Region::class, $this->region->id);

    expect($key)->toBe("region_data_{$this->region->id}");
});

test('it builds correct cache key for county', function () {
    $key = $this->cacheService->buildLocationKey(County::class, $this->county->id);

    expect($key)->toBe("county_data_{$this->county->id}");
});

test('it builds correct cache key for city', function () {
    $key = $this->cacheService->buildLocationKey(City::class, $this->city->id);

    expect($key)->toBe("city_data_{$this->city->id}");
});

test('it allows custom TTL', function () {
    $result = $this->cacheService->remember('test_key', fn() => 'test_value', 60);

    expect($result)->toBe('test_value');
});

test('it handles non-existent location gracefully when clearing cache', function () {
    // Should not throw exception for non-existent IDs
    $this->cacheService->clearForLocation(City::class, 99999);
    $this->cacheService->clearForLocation(County::class, 99999);
    $this->cacheService->clearForLocation(Region::class, 99999);

    expect(true)->toBeTrue();
});
