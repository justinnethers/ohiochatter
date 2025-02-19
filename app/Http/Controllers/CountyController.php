<?php

namespace App\Http\Controllers;

use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Region;
use App\Models\Content;

class CountyController extends Controller
{
    public function show(Region $region, County $county)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $featuredContent = Content::where('locatable_type', County::class)
            ->where('locatable_id', $county->id)
            ->with(['contentCategory', 'contentType'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        // Get content from cities in this county
        $cityIds = $county->cities->pluck('id');
        $cityContent = Content::where('locatable_type', City::class)
            ->whereIn('locatable_id', $cityIds)
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        // Get categories for the county
        $categories = ContentCategory::whereHas('content', function ($query) use ($county) {
            $query->where('locatable_type', County::class)
                ->where('locatable_id', $county->id)
                ->published();
        })->get();

        $cities = $county->cities()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.counties.show', compact('region', 'county', 'cities', 'featuredContent', 'cityContent', 'categories'));
    }
}
