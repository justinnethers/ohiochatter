<?php

namespace Tests\Unit\Modules\Geography\Services;

use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\County;
use App\Models\Region;
use App\Models\User;
use App\Modules\Geography\Services\GeographySeoService;
use App\ValueObjects\SeoData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seoService = new GeographySeoService();

    $this->region = Region::factory()->create([
        'name' => 'Central Ohio',
        'slug' => 'central-ohio',
        'description' => 'The heart of Ohio',
        'meta_title' => 'Central Ohio Guide',
        'meta_description' => 'Explore Central Ohio',
    ]);

    $this->county = County::factory()->create([
        'name' => 'Franklin County',
        'slug' => 'franklin',
        'region_id' => $this->region->id,
        'description' => 'Home to Columbus',
        'meta_title' => 'Franklin County Guide',
        'meta_description' => 'Explore Franklin County',
    ]);

    $this->city = City::factory()->create([
        'name' => 'Columbus',
        'slug' => 'columbus',
        'county_id' => $this->county->id,
        'description' => "Ohio's capital city",
        'meta_title' => 'Columbus Ohio Guide',
        'meta_description' => 'Explore Columbus',
        'population' => 905748,
        'coordinates' => ['lat' => 39.9612, 'lng' => -82.9988],
    ]);

    $this->category = ContentCategory::factory()->create([
        'name' => 'Restaurants',
        'slug' => 'restaurants',
    ]);

    $this->contentType = ContentType::factory()->create();
    $this->author = User::factory()->create();
});

test('forRegionsIndex returns proper SeoData', function () {
    $seo = $this->seoService->forRegionsIndex();

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toContain('Ohio')
        ->and($seo->description)->not->toBeEmpty()
        ->and($seo->canonical)->toBe(route('ohio.index'))
        ->and($seo->breadcrumbs)->toBeArray()
        ->and($seo->breadcrumbs)->toHaveCount(2);
});

test('forRegion returns proper SeoData with custom meta', function () {
    $seo = $this->seoService->forRegion($this->region);

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toBe('Central Ohio Guide')
        ->and($seo->description)->toBe('Explore Central Ohio')
        ->and($seo->canonical)->toBe(route('region.show', $this->region))
        ->and($seo->ogType)->toBe('place');
});

test('forRegion returns default meta when custom not set', function () {
    $region = Region::factory()->create([
        'name' => 'Northeast Ohio',
        'meta_title' => '',
        'meta_description' => '',
    ]);

    $seo = $this->seoService->forRegion($region);

    expect($seo->title)->toContain('Northeast Ohio')
        ->and($seo->description)->toContain('Northeast Ohio');
});

test('forCounty returns proper SeoData', function () {
    $seo = $this->seoService->forCounty($this->region, $this->county);

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toBe('Franklin County Guide')
        ->and($seo->canonical)->toBe(route('county.show', [$this->region, $this->county]))
        ->and($seo->breadcrumbs)->toHaveCount(4); // Home, Ohio, Region, County
});

test('forCity returns proper SeoData', function () {
    $seo = $this->seoService->forCity($this->region, $this->county, $this->city);

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toBe('Columbus Ohio Guide')
        ->and($seo->canonical)->toBe(route('city.show', [$this->region, $this->county, $this->city]))
        ->and($seo->breadcrumbs)->toHaveCount(5); // Home, Ohio, Region, County, City
});

test('forContent returns proper SeoData', function () {
    $content = Content::factory()->create([
        'title' => 'Best Burgers in Columbus',
        'excerpt' => 'A guide to the best burgers',
        'meta_title' => 'Best Burgers Guide',
        'meta_description' => 'Find amazing burgers',
        'content_type_id' => $this->contentType->id,
        'content_category_id' => $this->category->id,
        'user_id' => $this->author->id,
        'locatable_type' => City::class,
        'locatable_id' => $this->city->id,
        'published_at' => now(),
    ]);

    $seo = $this->seoService->forContent($content);

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toBe('Best Burgers Guide')
        ->and($seo->description)->toBe('Find amazing burgers')
        ->and($seo->ogType)->toBe('article');
});

test('forCategory returns proper SeoData without location', function () {
    $seo = $this->seoService->forCategory($this->category);

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toContain('Restaurants')
        ->and($seo->title)->toContain('Ohio');
});

test('forCategory returns proper SeoData with region', function () {
    $seo = $this->seoService->forCategory($this->category, $this->region);

    expect($seo->title)->toContain('Restaurants')
        ->and($seo->title)->toContain('Central Ohio');
});

test('forCategory returns proper SeoData with city', function () {
    $seo = $this->seoService->forCategory($this->category, $this->region, $this->county, $this->city);

    expect($seo->title)->toContain('Restaurants')
        ->and($seo->title)->toContain('Columbus');
});

test('forGuideIndex returns proper SeoData without location', function () {
    $seo = $this->seoService->forGuideIndex();

    expect($seo)->toBeInstanceOf(SeoData::class)
        ->and($seo->title)->toContain('Ohio Guide')
        ->and($seo->canonical)->toBe(route('guide.index'));
});

test('forGuideIndex returns proper SeoData for region', function () {
    $seo = $this->seoService->forGuideIndex($this->region);

    expect($seo->title)->toContain('Central Ohio')
        ->and($seo->canonical)->toBe(route('guide.region', $this->region));
});

test('forGuideIndex returns proper SeoData for county', function () {
    $seo = $this->seoService->forGuideIndex($this->region, $this->county);

    expect($seo->title)->toContain('Franklin County')
        ->and($seo->canonical)->toBe(route('guide.county', [$this->region, $this->county]));
});

test('forGuideIndex returns proper SeoData for city', function () {
    $seo = $this->seoService->forGuideIndex($this->region, $this->county, $this->city);

    expect($seo->title)->toContain('Columbus')
        ->and($seo->canonical)->toBe(route('guide.city', [$this->region, $this->county, $this->city]));
});

test('region SEO includes jsonLd schema', function () {
    $seo = $this->seoService->forRegion($this->region);

    expect($seo->jsonLd)->toBeArray()
        ->and($seo->jsonLd)->not->toBeEmpty();
});

test('city SEO includes population in schema when available', function () {
    $seo = $this->seoService->forCity($this->region, $this->county, $this->city);

    $citySchema = collect($seo->jsonLd)->first(fn($item) => ($item['@type'] ?? null) === 'City');

    expect($citySchema)->not->toBeNull()
        ->and($citySchema['population'])->toBe(905748);
});

test('city SEO includes geo coordinates when available', function () {
    $seo = $this->seoService->forCity($this->region, $this->county, $this->city);

    $citySchema = collect($seo->jsonLd)->first(fn($item) => ($item['@type'] ?? null) === 'City');

    expect($citySchema['geo'])->toBeArray()
        ->and($citySchema['geo']['latitude'])->toBe(39.9612)
        ->and($citySchema['geo']['longitude'])->toBe(-82.9988);
});
