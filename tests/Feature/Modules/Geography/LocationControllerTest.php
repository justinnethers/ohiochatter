<?php

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create([
        'name' => 'Central Ohio',
        'slug' => 'central-ohio',
    ]);

    $this->county = County::factory()->create([
        'region_id' => $this->region->id,
        'name' => 'Franklin County',
        'slug' => 'franklin',
    ]);

    $this->city = City::factory()->create([
        'county_id' => $this->county->id,
        'name' => 'Columbus',
        'slug' => 'columbus',
    ]);
});

describe('Regions Index', function () {
    it('displays regions index page', function () {
        $response = $this->get(route('ohio.index'));

        $response->assertOk();
        $response->assertViewIs('ohio.regions.index');
        $response->assertViewHas('regions');
        $response->assertViewHas('seo');
    });

    it('contains region data in view', function () {
        $response = $this->get(route('ohio.index'));

        $response->assertOk();
        $response->assertSee('Central Ohio');
    });
});

describe('Region Show', function () {
    it('displays a specific region', function () {
        $response = $this->get(route('region.show', $this->region));

        $response->assertOk();
        $response->assertViewIs('ohio.regions.show');
        $response->assertViewHas('region');
        $response->assertViewHas('counties');
        $response->assertViewHas('seo');
    });

    it('shows region name in view', function () {
        $response = $this->get(route('region.show', $this->region));

        $response->assertOk();
        $response->assertSee('Central Ohio');
    });

    it('returns 404 for non-existent region', function () {
        $response = $this->get('/ohio/non-existent-region');

        $response->assertNotFound();
    });
});

describe('County Show', function () {
    it('displays a specific county', function () {
        $response = $this->get(route('county.show', [
            'region' => $this->region,
            'county' => $this->county,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.counties.show');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('cities');
        $response->assertViewHas('seo');
    });

    it('shows county name in view', function () {
        $response = $this->get(route('county.show', [
            'region' => $this->region,
            'county' => $this->county,
        ]));

        $response->assertOk();
        $response->assertSee('Franklin County');
    });

    it('returns 404 for mismatched region-county', function () {
        $otherRegion = Region::factory()->create();

        $response = $this->get(route('county.show', [
            'region' => $otherRegion,
            'county' => $this->county,
        ]));

        $response->assertNotFound();
    });

    it('returns 404 for non-existent county', function () {
        $response = $this->get('/ohio/central-ohio/non-existent-county');

        $response->assertNotFound();
    });
});

describe('City Show', function () {
    it('displays a specific city', function () {
        $response = $this->get(route('city.show', [
            'region' => $this->region,
            'county' => $this->county,
            'city' => $this->city,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.cities.show');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('city');
        $response->assertViewHas('seo');
    });

    it('shows city name in view', function () {
        $response = $this->get(route('city.show', [
            'region' => $this->region,
            'county' => $this->county,
            'city' => $this->city,
        ]));

        $response->assertOk();
        $response->assertSee('Columbus');
    });

    it('returns 404 for mismatched county-city', function () {
        $otherCounty = County::factory()->create(['region_id' => $this->region->id]);

        $response = $this->get(route('city.show', [
            'region' => $this->region,
            'county' => $otherCounty,
            'city' => $this->city,
        ]));

        $response->assertNotFound();
    });

    it('returns 404 for mismatched region in city route', function () {
        $otherRegion = Region::factory()->create();
        $otherCounty = County::factory()->create(['region_id' => $otherRegion->id]);

        $response = $this->get(route('city.show', [
            'region' => $otherRegion,
            'county' => $this->county,
            'city' => $this->city,
        ]));

        $response->assertNotFound();
    });

    it('returns 404 for non-existent city', function () {
        $response = $this->get('/ohio/central-ohio/franklin/non-existent-city');

        $response->assertNotFound();
    });
});
