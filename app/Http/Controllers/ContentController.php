<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Services\LocationService;
use App\Traits\HandlesLocationContent;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    use HandlesLocationContent;

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

        return view('ohio.guide.index', compact('featuredContent', 'recentContent', 'categories'));
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

        return view('ohio.guide.categories', compact('categories'));
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

        return view('ohio.guide.category', compact('category', 'content'));
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

        return view('ohio.guide.show', compact('content', 'relatedContent'));
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

        return view('ohio.guide.region', [
            'region' => $region,
            'content' => $content,
            'categories' => $categories
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

        return view('ohio.guide.region-category', compact('region', 'category', 'content'));
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
        // Hierarchy validation handled by route model binding
        $locationService = app(LocationService::class);
        $content = $locationService->getAllLocationContent(County::class, $county->id);
        $categories = $locationService->getCategoriesForLocation(County::class, $county->id);
        $countyData = $locationService->getCountyData($county);

        return view('ohio.guide.county', [
            'region' => $region,
            'county' => $county,
            'content' => $content,
            'categories' => $categories,
            'cityContent' => $countyData['childContent']
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
        // Hierarchy validation handled by route model binding
        $locationService = app(LocationService::class);
        $content = $locationService->getLocationCategoryContent(
            County::class,
            $county->id,
            $category->id
        );

        return view('ohio.guide.county-category', compact('region', 'county', 'category', 'content'));
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
        // Hierarchy validation handled by route model binding
        $locationService = app(LocationService::class);
        $content = $locationService->getAllLocationContent(City::class, $city->id);
        $categories = $locationService->getCategoriesForLocation(City::class, $city->id);

        return view('ohio.guide.city', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'content' => $content,
            'categories' => $categories
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
        // Hierarchy validation handled by route model binding
        $locationService = app(LocationService::class);
        $content = $locationService->getLocationCategoryContent(
            City::class,
            $city->id,
            $category->id
        );

        return view('ohio.guide.city-category', compact('region', 'county', 'city', 'category', 'content'));
    }
}
