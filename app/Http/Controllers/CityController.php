<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Models\Content;

class CityController extends Controller
{
    public function show(Region $region, County $county, City $city)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $featuredContent = Content::where('locatable_type', City::class)
            ->where('locatable_id', $city->id)
            ->with(['contentCategory', 'contentType'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        $categories = $city->content()
            ->with('contentCategory')
            ->published()
            ->get()
            ->pluck('contentCategory')
            ->unique();

        return view('ohio.cities.show', compact('region', 'county', 'city', 'featuredContent', 'categories'));
    }
}
