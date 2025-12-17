<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Services\LocationService;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService
    ) {}

    public function regions()
    {
        $regions = $this->locationService->getAllRegionsWithContent();
        
        return view('ohio.regions.index', compact('regions'));
    }

    public function showRegion(Region $region)
    {
        $data = $this->locationService->getRegionData($region);
        
        return view('ohio.regions.show', [
            'region' => $region,
            'counties' => $data['counties'],
            'featuredContent' => $data['featuredContent'],
            'countyContent' => $data['childContent'],
            'categories' => $data['categories']
        ]);
    }

    public function showCounty(Region $region, County $county)
    {
        // Hierarchy validation handled by route model binding
        $data = $this->locationService->getCountyData($county);
        
        return view('ohio.counties.show', [
            'region' => $region,
            'county' => $county,
            'cities' => $data['cities'],
            'featuredContent' => $data['featuredContent'],
            'cityContent' => $data['childContent'],
            'categories' => $data['categories']
        ]);
    }

    public function showCity(Region $region, County $county, City $city)
    {
        // Hierarchy validation handled by route model binding
        $data = $this->locationService->getCityData($city);
        
        return view('ohio.cities.show', [
            'region' => $region,
            'county' => $county,
            'city' => $city,
            'featuredContent' => $data['featuredContent'],
            'categories' => $data['categories']
        ]);
    }
}