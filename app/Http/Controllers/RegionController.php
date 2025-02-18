<?php

namespace App\Http\Controllers;

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

        $counties = $region->counties()
            ->withCount('content')
            ->orderBy('name')
            ->get();

        return view('ohio.regions.show', compact('region', 'counties', 'featuredContent'));
    }
}
