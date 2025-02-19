<?php

namespace App\Http\Controllers;

use App\Actions\Content\FetchLocationContent;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Region;
use App\Models\Content;

class CountyController extends Controller
{
    public function show(Region $region, County $county, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id) {
            abort(404);
        }

        $contentData = $contentFetcher->forCounty($county);

        $cities = $county->cities()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.counties.show', [
            'region' => $region,
            'county' => $county,
            'cities' => $cities,
            'featuredContent' => $contentData['featuredContent'],
            'cityContent' => $contentData['childContent'],
            'categories' => $contentData['categories']
        ]);
    }
}
