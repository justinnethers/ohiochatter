<?php

namespace App\Http\Controllers;

use App\Actions\Content\FetchLocationContent;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Region;
use App\Models\County;
use App\Models\City;
use Illuminate\Http\Request;

class ContentController extends Controller
{
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
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function region(Region $region, FetchLocationContent $contentFetcher)
    {
        $contentData = $contentFetcher->allContentForRegion($region);

        return view('ohio.guide.region', [
            'region' => $region,
            'content' => $contentData['content'],
            'categories' => $contentData['categories']
        ]);
    }

    /**
     * Display content for a specific category in a region
     *
     * @param Region $region
     * @param ContentCategory $category
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function regionCategory(Region $region, ContentCategory $category, FetchLocationContent $contentFetcher)
    {
        $content = $contentFetcher->contentForLocationCategory(
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
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function county(Region $region, County $county, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $contentData = $contentFetcher->allContentForCounty($county);
        $countyContentData = $contentFetcher->forCounty($county);

        return view('ohio.guide.county', [
            'region' => $region,
            'county' => $county,
            'content' => $contentData['content'],
            'categories' => $contentData['categories'],
            'cityContent' => $countyContentData['childContent']
        ]);
    }

    /**
     * Display content for a specific category in a county
     *
     * @param Region $region
     * @param County $county
     * @param ContentCategory $category
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function countyCategory(Region $region, County $county, ContentCategory $category, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $content = $contentFetcher->contentForLocationCategory(
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
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function city(Region $region, County $county, City $city, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $contentData = $contentFetcher->allContentForCity($city);

        return view('ohio.guide.city', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'content' => $contentData['content'],
            'categories' => $contentData['categories']
        ]);
    }

    /**
     * Display content for a specific category in a city
     *
     * @param Region $region
     * @param County $county
     * @param City $city
     * @param ContentCategory $category
     * @param FetchLocationContent $contentFetcher
     * @return \Illuminate\View\View
     */
    public function cityCategory(Region $region, County $county, City $city, ContentCategory $category, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $content = $contentFetcher->contentForLocationCategory(
            City::class,
            $city->id,
            $category->id
        );

        return view('ohio.guide.city-category', compact('region', 'county', 'city', 'category', 'content'));
    }
}
