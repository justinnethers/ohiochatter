<?php

namespace App\Http\Controllers;

use App\Actions\Content\FetchLocationContent;
use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Models\Content;

class CityController extends Controller
{
    public function show(Region $region, County $county, City $city, FetchLocationContent $contentFetcher)
    {
        if ($county->region_id !== $region->id || $city->county_id !== $county->id) {
            abort(404);
        }

        $contentData = $contentFetcher->forCity($city);

        return view('ohio.cities.show', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'featuredContent' => $contentData['featuredContent'],
            'categories' => $contentData['categories']
        ]);
    }
}
