<?php

namespace Tests\Unit\Modules\Geography\Queries;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\Queries\FetchLocationHierarchy;
use App\Modules\Geography\ValueObjects\LocationPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create();
    $this->county = County::factory()->create(['region_id' => $this->region->id]);
    $this->city = City::factory()->create(['county_id' => $this->county->id]);
    $this->otherRegion = Region::factory()->create();
    $this->otherCounty = County::factory()->create(['region_id' => $this->otherRegion->id]);

    $this->query = new FetchLocationHierarchy();
});

test('it returns LocationPath with region only', function () {
    $result = $this->query->execute($this->region);

    expect($result)->toBeInstanceOf(LocationPath::class)
        ->and($result->region)->toBe($this->region)
        ->and($result->county)->toBeNull()
        ->and($result->city)->toBeNull();
});

test('it returns LocationPath with region and county', function () {
    $result = $this->query->execute($this->region, $this->county);

    expect($result)->toBeInstanceOf(LocationPath::class)
        ->and($result->region)->toBe($this->region)
        ->and($result->county)->toBe($this->county)
        ->and($result->city)->toBeNull();
});

test('it returns LocationPath with full hierarchy', function () {
    $result = $this->query->execute($this->region, $this->county, $this->city);

    expect($result)->toBeInstanceOf(LocationPath::class)
        ->and($result->region)->toBe($this->region)
        ->and($result->county)->toBe($this->county)
        ->and($result->city)->toBe($this->city);
});

test('it aborts when county does not belong to region', function () {
    $this->query->execute($this->region, $this->otherCounty);
})->throws(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

test('it aborts when city does not belong to county', function () {
    $otherCity = City::factory()->create(['county_id' => $this->otherCounty->id]);

    $this->query->execute($this->region, $this->county, $otherCity);
})->throws(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

test('it validates hierarchy correctly', function () {
    expect(fn() => $this->query->validateHierarchy($this->region, $this->county, $this->city))
        ->not->toThrow(\Exception::class);
});

test('validateHierarchy throws for wrong county-region', function () {
    $this->query->validateHierarchy($this->region, $this->otherCounty);
})->throws(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

test('validateHierarchy throws for wrong city-county', function () {
    $otherCity = City::factory()->create(['county_id' => $this->otherCounty->id]);

    $this->query->validateHierarchy($this->region, $this->county, $otherCity);
})->throws(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
