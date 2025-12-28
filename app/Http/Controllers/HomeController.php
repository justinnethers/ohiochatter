<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        // User activity for authenticated users
        $threadsWithActivity = collect();
        $repScore = 0;

        if ($isAuthenticated) {
            $userId = auth()->id();

            // Get rep score from cache or calculate
            $dashboardData = Cache::remember("user:{$userId}:dashboard", 300, function () use ($userId) {
                $replyReps = DB::table('reps')
                    ->join('replies', function ($join) use ($userId) {
                        $join->on('reps.repped_id', '=', 'replies.id')
                            ->where('reps.repped_type', '=', Reply::class)
                            ->where('replies.user_id', '=', $userId);
                    })
                    ->count();

                $threadReps = DB::table('reps')
                    ->join('threads', function ($join) use ($userId) {
                        $join->on('reps.repped_id', '=', 'threads.id')
                            ->where('reps.repped_type', '=', Thread::class)
                            ->where('threads.user_id', '=', $userId);
                    })
                    ->count();

                $replyNegs = DB::table('negs')
                    ->join('replies', function ($join) use ($userId) {
                        $join->on('negs.negged_id', '=', 'replies.id')
                            ->where('negs.negged_type', '=', Reply::class)
                            ->where('replies.user_id', '=', $userId);
                    })
                    ->count();

                $threadNegs = DB::table('negs')
                    ->join('threads', function ($join) use ($userId) {
                        $join->on('negs.negged_id', '=', 'threads.id')
                            ->where('negs.negged_type', '=', Thread::class)
                            ->where('threads.user_id', '=', $userId);
                    })
                    ->count();

                return [
                    'totalReps' => $replyReps + $threadReps,
                    'totalNegs' => $replyNegs + $threadNegs,
                ];
            });

            $repScore = $dashboardData['totalReps'] - $dashboardData['totalNegs'];

            // Threads with new activity
            $threadIdsWithActivity = DB::select("
                SELECT t.id, lr.created_at as last_reply_at
                FROM threads t
                INNER JOIN (
                    SELECT thread_id, MAX(id) as last_reply_id
                    FROM replies
                    GROUP BY thread_id
                ) latest ON t.id = latest.thread_id
                INNER JOIN replies lr ON lr.id = latest.last_reply_id
                WHERE lr.user_id != ?
                AND t.id IN (SELECT DISTINCT thread_id FROM replies WHERE user_id = ?)
                ORDER BY lr.created_at DESC
                LIMIT 3
            ", [$userId, $userId]);

            if (!empty($threadIdsWithActivity)) {
                $threadsWithActivity = Thread::whereIn('id', collect($threadIdsWithActivity)->pluck('id'))
                    ->with(['lastReply.owner', 'forum'])
                    ->get()
                    ->sortByDesc(fn($t) => $t->lastReply?->created_at);
            }
        }

        return view('home', compact(
            'threads',
            'featuredGuides',
            'stats',
            'threadsWithActivity',
            'repScore'
        ));
    }
}
