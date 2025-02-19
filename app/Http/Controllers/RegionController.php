<?php

namespace App\Http\Controllers;

use App\Actions\Content\FetchLocationContent;
use App\Models\ContentCategory;
use App\Models\Region;
use App\Models\Content;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with(['content' => function ($query) {
            $query->published()->latest('published_at')->take(3);
        }])->get();

        return view('ohio.regions.index', compact('regions'));
    }

    public function show(Region $region, FetchLocationContent $contentFetcher)
    {
        $contentData = $contentFetcher->forRegion($region);

        $counties = $region->counties()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.regions.show', [
            'region' => $region,
            'counties' => $counties,
            'featuredContent' => $contentData['featuredContent'],
            'countyContent' => $contentData['childContent'],
            'categories' => $contentData['categories']
        ]);
    }
}
