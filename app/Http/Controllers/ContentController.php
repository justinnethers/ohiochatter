<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Region;
use App\Models\County;
use App\Models\City;

class ContentController extends Controller
{
    public function index()
    {
        $featuredContent = Content::with(['contentCategory', 'contentType', 'author', 'locatable'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        $categories = ContentCategory::withCount(['content' => function ($query) {
            $query->published();
        }])->get();

        return view('ohio.guide.index', compact('featuredContent', 'categories'));
    }

    public function categories()
    {
        $categories = ContentCategory::withCount(['content' => function ($query) {
            $query->published();
        }])->get();

        return view('ohio.guide.categories', compact('categories'));
    }

    public function category(ContentCategory $category)
    {
        $content = Content::where('content_category_id', $category->id)
            ->with(['contentType', 'author', 'locatable'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return view('ohio.guide.category', compact('category', 'content'));
    }

    public function show(Content $content)
    {
        if (!$content->published_at) {
            abort(404);
        }

        $relatedContent = $content->relatedContent()
            ->published()
            ->take(3)
            ->get();

        return view('ohio.guide.show', compact('content', 'relatedContent'));
    }

    public function region(Region $region)
    {
        $content = Content::where('locatable_type', Region::class)
            ->where('locatable_id', $region->id)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $categories = ContentCategory::whereHas('content', function ($query) use ($region) {
            $query->where('locatable_type', Region::class)
                ->where('locatable_id', $region->id)
                ->published();
        })->get();

        return view('ohio.guide.region', compact('region', 'content', 'categories'));
    }

    public function regionCategory(Region $region, ContentCategory $category)
    {
        $content = Content::where('locatable_type', Region::class)
            ->where('locatable_id', $region->id)
            ->where('content_category_id', $category->id)
            ->with(['contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return view('ohio.guide.region-category', compact('region', 'category', 'content'));
    }

    public function county(Region $region, County $county)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $content = Content::where('locatable_type', County::class)
            ->where('locatable_id', $county->id)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $categories = ContentCategory::whereHas('content', function ($query) use ($county) {
            $query->where('locatable_type', County::class)
                ->where('locatable_id', $county->id)
                ->published();
        })->get();

        return view('ohio.guide.county', compact('region', 'county', 'content', 'categories'));
    }

    public function countyCategory(Region $region, County $county, ContentCategory $category)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $content = Content::where('locatable_type', County::class)
            ->where('locatable_id', $county->id)
            ->where('content_category_id', $category->id)
            ->with(['contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return view('ohio.guide.county-category', compact('region', 'county', 'category', 'content'));
    }

    public function city(Region $region, County $county, City $city)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $content = Content::where('locatable_type', City::class)
            ->where('locatable_id', $city->id)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $categories = ContentCategory::whereHas('content', function ($query) use ($city) {
            $query->where('locatable_type', City::class)
                ->where('locatable_id', $city->id)
                ->published();
        })->get();

        return view('ohio.guide.city', compact('region', 'county', 'city', 'content', 'categories'));
    }

    public function cityCategory(Region $region, County $county, City $city, ContentCategory $category)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $content = Content::where('locatable_type', City::class)
            ->where('locatable_id', $city->id)
            ->where('content_category_id', $category->id)
            ->with(['contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return view('ohio.guide.city-category', compact('region', 'county', 'city', 'category', 'content'));
    }
}
