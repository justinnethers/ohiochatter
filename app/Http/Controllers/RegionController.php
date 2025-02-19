<?php

namespace App\Http\Controllers;

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

    public function show(Region $region)
    {
        $featuredContent = Content::where('locatable_type', Region::class)
            ->where('locatable_id', $region->id)
            ->with(['contentCategory', 'contentType'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        // Get content from counties in this region
        $countyIds = $region->counties->pluck('id');
        $countyContent = Content::where('locatable_type', County::class)
            ->whereIn('locatable_id', $countyIds)
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        // Get categories for the region
        $categories = ContentCategory::whereHas('content', function ($query) use ($region) {
            $query->where('locatable_type', Region::class)
                ->where('locatable_id', $region->id)
                ->published();
        })->get();

        $counties = $region->counties()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.regions.show', compact('region', 'counties', 'featuredContent', 'countyContent', 'categories'));
    }
}
