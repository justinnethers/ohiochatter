<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Services\LocationService;
use App\Services\SeoService;
use App\Traits\HandlesLocationContent;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    use HandlesLocationContent;

    public function __construct(
        private SeoService $seoService
    ) {}

    /**
     * Display a listing of all content
     *
     * @return \Illuminate\View\View
     */
    public function index()
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

    /**
     * Display a listing of all categories
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        $categories = ContentCategory::withCount(['content' => function ($query) {
            $query->published();
        }])->get();

        $seo = new \App\ValueObjects\SeoData(
            title: 'Ohio Guide Categories | Browse All Topics',
            description: 'Explore all categories in our Ohio guide. Find restaurants, attractions, things to do, and more across the Buckeye State.',
            canonical: route('ohio.guide.categories'),
            breadcrumbs: [
                ['name' => 'Home', 'url' => config('app.url')],
                ['name' => 'Guide', 'url' => route('ohio.guide.index')],
                ['name' => 'Categories'],
            ],
        );

        return view('ohio.guide.categories', compact('categories', 'seo'));
    }

    /**
     * Display a specific category
     *
     * @param ContentCategory $category
     * @return \Illuminate\View\View
     */
    public function category(ContentCategory $category)
    {
        $content = Content::where('content_category_id', $category->id)
            ->with(['contentType', 'author', 'locatable'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $seo = $this->seoService->forCategory($category);

        return view('ohio.guide.category', compact('category', 'content', 'seo'));
    }

    /**
     * Display a specific content item
     *
     * @param Content $content
     * @return \Illuminate\View\View
     */
    public function show(Content $content)
    {
        if (!$content->published_at) {
//            abort(404);
        }

        $relatedContent = $content->relatedContent()
            ->published()
            ->take(3)
            ->get();

        $seo = $this->seoService->forContent($content);

        return view('ohio.guide.show', compact('content', 'relatedContent', 'seo'));
    }

    /**
     * Display content for a specific region
     *
     * @param Region $region
     * @return \Illuminate\View\View
     */
    public function region(Region $region)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getAllLocationContent(Region::class, $region->id);
        $categories = $locationService->getCategoriesForLocation(Region::class, $region->id);
        $seo = $this->seoService->forGuideIndex($region);

        return view('ohio.guide.region', [
            'region' => $region,
            'content' => $content,
            'categories' => $categories,
            'seo' => $seo,
        ]);
    }

    /**
     * Display content for a specific category in a region
     *
     * @param Region $region
     * @param ContentCategory $category
     * @return \Illuminate\View\View
     */
    public function regionCategory(Region $region, ContentCategory $category)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getLocationCategoryContent(
            Region::class,
            $region->id,
            $category->id
        );
        $seo = $this->seoService->forCategory($category, $region);

        return view('ohio.guide.region-category', compact('region', 'category', 'content', 'seo'));
    }

    /**
     * Display content for a specific county
     *
     * @param Region $region
     * @param County $county
     * @return \Illuminate\View\View
     */
    public function county(Region $region, County $county)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getAllLocationContent(County::class, $county->id);
        $categories = $locationService->getCategoriesForLocation(County::class, $county->id);
        $countyData = $locationService->getCountyData($county);
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

    /**
     * Display content for a specific category in a county
     *
     * @param Region $region
     * @param County $county
     * @param ContentCategory $category
     * @return \Illuminate\View\View
     */
    public function countyCategory(Region $region, County $county, ContentCategory $category)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getLocationCategoryContent(
            County::class,
            $county->id,
            $category->id
        );
        $seo = $this->seoService->forCategory($category, $region, $county);

        return view('ohio.guide.county-category', compact('region', 'county', 'category', 'content', 'seo'));
    }

    /**
     * Display content for a specific city
     *
     * @param Region $region
     * @param County $county
     * @param City $city
     * @return \Illuminate\View\View
     */
    public function city(Region $region, County $county, City $city)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getAllLocationContent(City::class, $city->id);
        $categories = $locationService->getCategoriesForLocation(City::class, $city->id);
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

    /**
     * Display content for a specific category in a city
     *
     * @param Region $region
     * @param County $county
     * @param City $city
     * @param ContentCategory $category
     * @return \Illuminate\View\View
     */
    public function cityCategory(Region $region, County $county, City $city, ContentCategory $category)
    {
        $locationService = app(LocationService::class);
        $content = $locationService->getLocationCategoryContent(
            City::class,
            $city->id,
            $category->id
        );
        $seo = $this->seoService->forCategory($category, $region, $county, $city);

        return view('ohio.guide.city-category', compact('region', 'county', 'city', 'category', 'content', 'seo'));
    }
}
