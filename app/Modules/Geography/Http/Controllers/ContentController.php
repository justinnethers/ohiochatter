<?php

namespace App\Modules\Geography\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\Queries\FetchCategoriesForLocation;
use App\Modules\Geography\Queries\FetchCountyData;
use App\Modules\Geography\Queries\FetchLocationContent;
use App\Modules\Geography\Queries\FetchLocationHierarchy;
use App\Modules\Geography\Services\GeographySeoService;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(
        private GeographySeoService $seoService,
        private FetchLocationHierarchy $fetchHierarchy,
        private FetchLocationContent $fetchContent,
        private FetchCategoriesForLocation $fetchCategories
    ) {}

    public function index(): View
    {
        $featuredContent = Content::with(['contentCategory', 'contentType', 'author', 'locatable'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        $recentContent = Content::with(['contentCategory', 'contentType', 'author', 'locatable'])
            ->published()
            ->latest('published_at')
            ->paginate(10);

        $categories = ContentCategory::withCount(['content' => function ($query) {
            $query->published();
        }])->get();

        $seo = $this->seoService->forGuideIndex();

        return view('ohio.guide.index', compact('featuredContent', 'recentContent', 'categories', 'seo'));
    }

    public function categories(): View
    {
        $categories = ContentCategory::withCount(['content' => function ($query) {
            $query->published();
        }])->get();

        $seo = new \App\ValueObjects\SeoData(
            title: 'Ohio Guide Categories | Browse All Topics',
            description: 'Explore all categories in our Ohio guide. Find restaurants, attractions, things to do, and more across the Buckeye State.',
            canonical: route('guide.categories'),
            breadcrumbs: [
                ['name' => 'Home', 'url' => config('app.url')],
                ['name' => 'Guide', 'url' => route('guide.index')],
                ['name' => 'Categories'],
            ],
        );

        return view('ohio.guide.categories', compact('categories', 'seo'));
    }

    public function category(ContentCategory $category): View
    {
        $content = Content::where('content_category_id', $category->id)
            ->with(['contentType', 'author', 'locatable'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $seo = $this->seoService->forCategory($category);

        return view('ohio.guide.category', compact('category', 'content', 'seo'));
    }

    public function show(Content $content): View
    {
        $relatedContent = $content->relatedContent()
            ->published()
            ->take(3)
            ->get();

        $seo = $this->seoService->forContent($content);

        return view('ohio.guide.show', compact('content', 'relatedContent', 'seo'));
    }

    public function region(Region $region): View
    {
        $content = $this->fetchContent->execute(Region::class, $region->id);
        $categories = $this->fetchCategories->execute(Region::class, $region->id);
        $seo = $this->seoService->forGuideIndex($region);

        return view('ohio.guide.region', [
            'region' => $region,
            'content' => $content,
            'categories' => $categories,
            'seo' => $seo,
        ]);
    }

    public function regionCategory(Region $region, ContentCategory $category): View
    {
        $content = $this->fetchContent->forCategory(Region::class, $region->id, $category->id);
        $seo = $this->seoService->forCategory($category, $region);

        return view('ohio.guide.region-category', compact('region', 'category', 'content', 'seo'));
    }

    public function county(Region $region, County $county, FetchCountyData $countyQuery): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county);

        $content = $this->fetchContent->execute(County::class, $county->id);
        $categories = $this->fetchCategories->execute(County::class, $county->id);
        $countyData = $countyQuery->execute($county);
        $seo = $this->seoService->forGuideIndex($region, $county);

        return view('ohio.guide.county', [
            'region' => $region,
            'county' => $county,
            'content' => $content,
            'categories' => $categories,
            'cityContent' => $countyData['childContent'],
            'seo' => $seo,
        ]);
    }

    public function countyCategory(Region $region, County $county, ContentCategory $category): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county);

        $content = $this->fetchContent->forCategory(County::class, $county->id, $category->id);
        $seo = $this->seoService->forCategory($category, $region, $county);

        return view('ohio.guide.county-category', compact('region', 'county', 'category', 'content', 'seo'));
    }

    public function city(Region $region, County $county, City $city): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county, $city);

        $content = $this->fetchContent->execute(City::class, $city->id);
        $categories = $this->fetchCategories->execute(City::class, $city->id);
        $seo = $this->seoService->forGuideIndex($region, $county, $city);

        return view('ohio.guide.city', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'content' => $content,
            'categories' => $categories,
            'seo' => $seo,
        ]);
    }

    public function cityCategory(Region $region, County $county, City $city, ContentCategory $category): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county, $city);

        $content = $this->fetchContent->forCategory(City::class, $city->id, $category->id);
        $seo = $this->seoService->forCategory($category, $region, $county, $city);

        return view('ohio.guide.city-category', compact('region', 'county', 'city', 'category', 'content', 'seo'));
    }
}
