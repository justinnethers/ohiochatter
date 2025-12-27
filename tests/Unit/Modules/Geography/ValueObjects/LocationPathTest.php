<?php

namespace Tests\Unit\Modules\Geography\ValueObjects;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\ValueObjects\LocationPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create([
        'name' => 'Central Ohio',
        'slug' => 'central-ohio',
    ]);

    $this->county = County::factory()->create([
        'name' => 'Franklin County',
        'slug' => 'franklin',
        'region_id' => $this->region->id,
    ]);

    $this->city = City::factory()->create([
        'name' => 'Columbus',
        'slug' => 'columbus',
        'county_id' => $this->county->id,
    ]);
});

test('it can be created with only a region', function () {
    $path = new LocationPath(region: $this->region);

    expect($path->region)->toBe($this->region)
        ->and($path->county)->toBeNull()
        ->and($path->city)->toBeNull();
});

test('it can be created with region and county', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county
    );

    expect($path->region)->toBe($this->region)
        ->and($path->county)->toBe($this->county)
        ->and($path->city)->toBeNull();
});

test('it can be created with full hierarchy', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    expect($path->region)->toBe($this->region)
        ->and($path->county)->toBe($this->county)
        ->and($path->city)->toBe($this->city);
});

test('getMostSpecificLocation returns city when all are set', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    expect($path->getMostSpecificLocation())->toBe($this->city);
});

test('getMostSpecificLocation returns county when city is null', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county
    );

    expect($path->getMostSpecificLocation())->toBe($this->county);
});

test('getMostSpecificLocation returns region when county is null', function () {
    $path = new LocationPath(region: $this->region);

    expect($path->getMostSpecificLocation())->toBe($this->region);
});

test('getMostSpecificLocation returns null when empty', function () {
    $path = new LocationPath();

    expect($path->getMostSpecificLocation())->toBeNull();
});

test('getLocatableType returns City class for city', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    expect($path->getLocatableType())->toBe(City::class);
});

test('getLocatableType returns County class for county', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county
    );

    expect($path->getLocatableType())->toBe(County::class);
});

test('getLocatableType returns Region class for region', function () {
    $path = new LocationPath(region: $this->region);

    expect($path->getLocatableType())->toBe(Region::class);
});

test('getLocatableType returns null when empty', function () {
    $path = new LocationPath();

    expect($path->getLocatableType())->toBeNull();
});

test('getLocatableId returns correct id', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    expect($path->getLocatableId())->toBe($this->city->id);
});

test('getLocatableId returns null when empty', function () {
    $path = new LocationPath();

    expect($path->getLocatableId())->toBeNull();
});

test('toBreadcrumbs returns region breadcrumb', function () {
    $path = new LocationPath(region: $this->region);

    $breadcrumbs = $path->toBreadcrumbs();

    expect($breadcrumbs)->toHaveKey('region')
        ->and($breadcrumbs['region']['name'])->toBe('Central Ohio')
        ->and($breadcrumbs['region']['url'])->toBe(route('region.show', $this->region));
});

test('toBreadcrumbs returns region and county breadcrumbs', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county
    );

    $breadcrumbs = $path->toBreadcrumbs();

    expect($breadcrumbs)->toHaveKeys(['region', 'county'])
        ->and($breadcrumbs['county']['name'])->toBe('Franklin County')
        ->and($breadcrumbs['county']['url'])->toBe(route('county.show', [$this->region, $this->county]));
});

test('toBreadcrumbs returns full hierarchy breadcrumbs', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    $breadcrumbs = $path->toBreadcrumbs();

    expect($breadcrumbs)->toHaveKeys(['region', 'county', 'city'])
        ->and($breadcrumbs['city']['name'])->toBe('Columbus')
        ->and($breadcrumbs['city']['url'])->toBe(route('city.show', [$this->region, $this->county, $this->city]));
});

test('toBreadcrumbs returns empty array when empty', function () {
    $path = new LocationPath();

    expect($path->toBreadcrumbs())->toBe([]);
});

test('getLocationName returns city name when city is set', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    expect($path->getLocationName())->toBe('Columbus');
});

test('getLocationName returns county name with suffix when county is set', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county
    );

    expect($path->getLocationName())->toBe('Franklin County');
});

test('getLocationName returns region name when only region is set', function () {
    $path = new LocationPath(region: $this->region);

    expect($path->getLocationName())->toBe('Central Ohio');
});

test('getLocationName returns null when empty', function () {
    $path = new LocationPath();

    expect($path->getLocationName())->toBeNull();
});

test('it is immutable with readonly properties', function () {
    $path = new LocationPath(
        region: $this->region,
        county: $this->county,
        city: $this->city
    );

    $reflection = new \ReflectionClass($path);

    expect($reflection->isReadOnly())->toBeTrue();
});
