<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Services\LocationService;
use App\Services\SeoService;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService,
        private SeoService $seoService
    ) {}

    public function regions()
    {
        $regions = $this->locationService->getAllRegionsWithContent();

        $seo = new \App\ValueObjects\SeoData(
            title: 'Explore Ohio Regions | Local Guides & Community',
            description: 'Discover all regions of Ohio. Find local guides, community discussions, and resources for every corner of the Buckeye State.',
            canonical: route('ohio.index'),
            breadcrumbs: [
                ['name' => 'Home', 'url' => config('app.url')],
                ['name' => 'Ohio'],
            ],
        );

        return view('ohio.regions.index', compact('regions', 'seo'));
    }

    public function showRegion(Region $region)
    {
        $data = $this->locationService->getRegionData($region);
        $seo = $this->seoService->forRegion($region);

        return view('ohio.regions.show', [
            'region' => $region,
            'counties' => $data['counties'],
            'featuredContent' => $data['featuredContent'],
            'countyContent' => $data['childContent'],
            'categories' => $data['categories'],
            'seo' => $seo,
        ]);
    }

    public function showCounty(Region $region, County $county)
    {
        $data = $this->locationService->getCountyData($county);
        $seo = $this->seoService->forCounty($region, $county);

        return view('ohio.counties.show', [
            'region' => $region,
            'county' => $county,
            'cities' => $data['cities'],
            'featuredContent' => $data['featuredContent'],
            'cityContent' => $data['childContent'],
            'categories' => $data['categories'],
            'seo' => $seo,
        ]);
    }

    public function showCity(Region $region, County $county, City $city)
    {
        $data = $this->locationService->getCityData($city);
        $seo = $this->seoService->forCity($region, $county, $city);

        return view('ohio.cities.show', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'featuredContent' => $data['featuredContent'],
            'categories' => $data['categories'],
            'seo' => $seo,
        ]);
    }
}