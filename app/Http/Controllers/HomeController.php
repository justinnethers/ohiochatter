<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke()
    {
        $isAuthenticated = auth()->check();
        $cacheKey = $isAuthenticated ? 'homepage_threads_auth' : 'homepage_threads_guest';

        // Recent threads (cached for 5 minutes)
        $threads = Cache::remember($cacheKey, 300, function () use ($isAuthenticated) {
            $query = Thread::query()
                ->with(['owner', 'forum', 'poll'])
                ->orderBy('last_activity_at', 'desc');

            if (!$isAuthenticated) {
                $query->whereHas('forum', function ($q) {
                    $q->where('name', '!=', 'Politics');
                });
            }

            return $query->take(10)->get();
        });

        // Featured guides (cached for 1 hour)
        $featuredGuides = Cache::remember('homepage_featured_guides', 3600, function () {
            return Content::featured()
                ->published()
                ->with(['locatable', 'contentCategory'])
                ->latest('published_at')
                ->take(4)
                ->get();
        });

        // Community stats (cached for 15 minutes)
        $stats = Cache::remember('homepage_stats', 900, function () {
            return [
                'members' => User::count(),
                'threads' => Thread::count(),
                'replies' => Reply::count(),
            ];
        });

        return view('home', compact(
            'threads',
            'featuredGuides',
            'stats'
        ));
    }
}
