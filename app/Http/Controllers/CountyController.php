<?php

namespace App\Http\Controllers;

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

        $cities = $county->cities()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.counties.show', compact('region', 'county', 'cities', 'featuredContent'));
    }
}
