<?php

namespace App\Modules\Geography\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Content;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\Queries\FetchCityData;
use App\Modules\Geography\Queries\FetchCountyData;
use App\Modules\Geography\Queries\FetchLocationHierarchy;
use App\Modules\Geography\Queries\FetchRegionData;
use App\Modules\Geography\Queries\FetchRegionsWithContent;
use App\Modules\Geography\Services\GeographySeoService;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(
        private GeographySeoService $seoService,
        private FetchLocationHierarchy $fetchHierarchy
    ) {}

    public function index(FetchRegionsWithContent $query): View
    {
        $regions = $query->execute();
        $seo = $this->seoService->forRegionsIndex();
        $countyCount = County::count();
        $cityCount = City::count();
        $guideCount = Content::whereNotNull('published_at')->count();

        return view('ohio.regions.index', compact('regions', 'seo', 'countyCount', 'cityCount', 'guideCount'));
    }

    public function showRegion(Region $region, FetchRegionData $query): View
    {
        $data = $query->execute($region);
        $seo = $this->seoService->forRegion($region);

        return view('ohio.regions.show', [
            'region' => $region,
            'counties' => $data['counties'],
            'featuredContent' => $data['featuredContent'],
            'countyContent' => $data['childContent'],
            'categories' => $data['categories'],
            'totalContentCount' => $data['totalContentCount'],
            'seo' => $seo,
        ]);
    }

    public function showCounty(Region $region, County $county, FetchCountyData $query): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county);

        $data = $query->execute($county);
        $seo = $this->seoService->forCounty($region, $county);

        return view('ohio.counties.show', [
            'region' => $region,
            'county' => $county,
            'cities' => $data['cities'],
            'featuredContent' => $data['featuredContent'],
            'cityContent' => $data['childContent'],
            'categories' => $data['categories'],
            'totalContentCount' => $data['totalContentCount'],
            'seo' => $seo,
        ]);
    }

    public function showCity(Region $region, County $county, City $city, FetchCityData $query): View
    {
        $this->fetchHierarchy->validateHierarchy($region, $county, $city);

        $data = $query->execute($city);
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
