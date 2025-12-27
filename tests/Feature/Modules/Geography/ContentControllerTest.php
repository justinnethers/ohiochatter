<?php

use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\County;
use App\Models\Region;
use App\Models\User;
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

    $this->category = ContentCategory::factory()->create([
        'name' => 'Restaurants',
        'slug' => 'restaurants',
    ]);

    $this->contentType = ContentType::factory()->create();
    $this->user = User::factory()->create();
});

describe('Guide Index', function () {
    it('displays guide index page', function () {
        $response = $this->get(route('guide.index'));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.index');
        $response->assertViewHas('featuredContent');
        $response->assertViewHas('recentContent');
        $response->assertViewHas('categories');
        $response->assertViewHas('seo');
    });

    it('shows featured content on guide index', function () {
        $content = Content::factory()->create([
            'content_type_id' => $this->contentType->id,
            'content_category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'title' => 'Featured Restaurant Guide',
            'featured' => true,
            'published_at' => now(),
        ]);

        $response = $this->get(route('guide.index'));

        $response->assertOk();
        $response->assertSee('Featured Restaurant Guide');
    });
});

describe('Categories', function () {
    it('displays categories page', function () {
        $response = $this->get(route('guide.categories'));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.categories');
        $response->assertViewHas('categories');
        $response->assertViewHas('seo');
    });

    it('shows category on categories page', function () {
        $response = $this->get(route('guide.categories'));

        $response->assertOk();
        $response->assertSee('Restaurants');
    });
});

describe('Category Show', function () {
    it('displays a specific category', function () {
        $response = $this->get(route('guide.category', $this->category));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.category');
        $response->assertViewHas('category');
        $response->assertViewHas('content');
        $response->assertViewHas('seo');
    });

    it('shows category content', function () {
        Content::factory()->create([
            'content_type_id' => $this->contentType->id,
            'content_category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'title' => 'Best Pizza Places',
            'published_at' => now(),
        ]);

        $response = $this->get(route('guide.category', $this->category));

        $response->assertOk();
        $response->assertSee('Best Pizza Places');
    });

    it('returns 404 for non-existent category', function () {
        $response = $this->get('/ohio/guide/category/non-existent-category');

        $response->assertNotFound();
    });
});

describe('Content Show', function () {
    it('displays a specific content article', function () {
        $content = Content::factory()->create([
            'content_type_id' => $this->contentType->id,
            'content_category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'title' => 'Top 10 Coffee Shops',
            'published_at' => now(),
        ]);

        $response = $this->get(route('guide.show', $content));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.show');
        $response->assertViewHas('content');
        $response->assertViewHas('relatedContent');
        $response->assertViewHas('seo');
    });

    it('shows article title', function () {
        $content = Content::factory()->create([
            'content_type_id' => $this->contentType->id,
            'content_category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'title' => 'Amazing Hiking Trails',
            'published_at' => now(),
        ]);

        $response = $this->get(route('guide.show', $content));

        $response->assertOk();
        $response->assertSee('Amazing Hiking Trails');
    });
});

describe('Region Guide', function () {
    it('displays region guide page', function () {
        $response = $this->get(route('guide.region', $this->region));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.region');
        $response->assertViewHas('region');
        $response->assertViewHas('content');
        $response->assertViewHas('categories');
        $response->assertViewHas('seo');
    });

    it('shows region name on guide page', function () {
        $response = $this->get(route('guide.region', $this->region));

        $response->assertOk();
        $response->assertSee('Central Ohio');
    });
});

describe('Region Category', function () {
    it('displays region category page', function () {
        $response = $this->get(route('guide.region.category', [
            'region' => $this->region,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.region-category');
        $response->assertViewHas('region');
        $response->assertViewHas('category');
        $response->assertViewHas('content');
        $response->assertViewHas('seo');
    });
});

describe('County Guide', function () {
    it('displays county guide page', function () {
        $response = $this->get(route('guide.county', [
            'region' => $this->region,
            'county' => $this->county,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.county');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('content');
        $response->assertViewHas('categories');
        $response->assertViewHas('seo');
    });

    it('validates region-county hierarchy', function () {
        $otherRegion = Region::factory()->create();

        $response = $this->get(route('guide.county', [
            'region' => $otherRegion,
            'county' => $this->county,
        ]));

        $response->assertNotFound();
    });
});

describe('County Category', function () {
    it('displays county category page', function () {
        $response = $this->get(route('guide.county.category', [
            'region' => $this->region,
            'county' => $this->county,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.county-category');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('category');
        $response->assertViewHas('content');
        $response->assertViewHas('seo');
    });
});

describe('City Guide', function () {
    it('displays city guide page', function () {
        $response = $this->get(route('guide.city', [
            'region' => $this->region,
            'county' => $this->county,
            'city' => $this->city,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.city');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('city');
        $response->assertViewHas('content');
        $response->assertViewHas('categories');
        $response->assertViewHas('seo');
    });

    it('validates full hierarchy for city', function () {
        $otherRegion = Region::factory()->create();

        $response = $this->get(route('guide.city', [
            'region' => $otherRegion,
            'county' => $this->county,
            'city' => $this->city,
        ]));

        $response->assertNotFound();
    });
});

describe('City Category', function () {
    it('displays city category page', function () {
        $response = $this->get(route('guide.city.category', [
            'region' => $this->region,
            'county' => $this->county,
            'city' => $this->city,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.city-category');
        $response->assertViewHas('region');
        $response->assertViewHas('county');
        $response->assertViewHas('city');
        $response->assertViewHas('category');
        $response->assertViewHas('content');
        $response->assertViewHas('seo');
    });
});

describe('Best Of Routes (aliases)', function () {
    it('region best route works same as category route', function () {
        $response = $this->get(route('region.best', [
            'region' => $this->region,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.region-category');
    });

    it('county best route works same as category route', function () {
        $response = $this->get(route('county.best', [
            'region' => $this->region,
            'county' => $this->county,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.county-category');
    });

    it('city best route works same as category route', function () {
        $response = $this->get(route('city.best', [
            'region' => $this->region,
            'county' => $this->county,
            'city' => $this->city,
            'category' => $this->category,
        ]));

        $response->assertOk();
        $response->assertViewIs('ohio.guide.city-category');
    });
});
