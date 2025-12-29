<?php

namespace App\Http\Controllers;

use App\Models\Neg;
use App\Models\Rep;
use App\Models\Reply;
use App\Models\Thread;
use App\Services\ReplyPaginationService;
use Cmgmyr\Messenger\Models\Thread as MessageThread;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $userId = $user->id;

        // Track dashboard visits (throttled to once per minute)
        $visitKey = "dashboard_visit:{$userId}";
        if (!Cache::has($visitKey)) {
            $user->increment('dashboard_visits');
            Cache::put($visitKey, true, 60);
        }

        // Cache all expensive dashboard data for 2 minutes
        $dashboardData = Cache::remember("user:{$userId}:dashboard_full", 120, function () use ($userId) {
            // Rep counts
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

            // Recent rep activity with position for direct post linking
            $reps = DB::table('reps')
                ->join('replies', function ($join) use ($userId) {
                    $join->on('reps.repped_id', '=', 'replies.id')
                        ->where('reps.repped_type', '=', Reply::class)
                        ->where('replies.user_id', '=', $userId);
                })
                ->join('threads', 'replies.thread_id', '=', 'threads.id')
                ->join('forums', 'threads.forum_id', '=', 'forums.id')
                ->join('users', 'reps.user_id', '=', 'users.id')
                ->select(
                    'reps.created_at',
                    'users.username',
                    'threads.title as thread_title',
                    'threads.slug as thread_slug',
                    'forums.slug as forum_slug',
                    'replies.id as reply_id',
                    DB::raw('(' . ReplyPaginationService::positionSubquery() . ') as reply_position')
                )
                ->orderByDesc('reps.created_at')
                ->limit(5)
                ->get()
                ->map(fn($r) => ['type' => 'rep', 'username' => $r->username, 'thread_title' => $r->thread_title, 'thread_slug' => $r->thread_slug, 'forum_slug' => $r->forum_slug, 'reply_id' => $r->reply_id, 'reply_position' => $r->reply_position, 'created_at' => $r->created_at]);

            $negs = DB::table('negs')
                ->join('replies', function ($join) use ($userId) {
                    $join->on('negs.negged_id', '=', 'replies.id')
                        ->where('negs.negged_type', '=', Reply::class)
                        ->where('replies.user_id', '=', $userId);
                })
                ->join('threads', 'replies.thread_id', '=', 'threads.id')
                ->join('forums', 'threads.forum_id', '=', 'forums.id')
                ->join('users', 'negs.user_id', '=', 'users.id')
                ->select(
                    'negs.created_at',
                    'users.username',
                    'threads.title as thread_title',
                    'threads.slug as thread_slug',
                    'forums.slug as forum_slug',
                    'replies.id as reply_id',
                    DB::raw('(' . ReplyPaginationService::positionSubquery() . ') as reply_position')
                )
                ->orderByDesc('negs.created_at')
                ->limit(5)
                ->get()
                ->map(fn($n) => ['type' => 'neg', 'username' => $n->username, 'thread_title' => $n->thread_title, 'thread_slug' => $n->thread_slug, 'forum_slug' => $n->forum_slug, 'reply_id' => $n->reply_id, 'reply_position' => $n->reply_position, 'created_at' => $n->created_at]);

            $recentRepActivity = $reps->merge($negs)
                ->sortByDesc('created_at')
                ->take(5)
                ->values()
                ->toArray();

            // User's recent threads
            $userThreads = Thread::where('user_id', $userId)
                ->with('forum')
                ->withCount('replies')
                ->latest()
                ->limit(5)
                ->get();

            // Threads with new activity - simplified query
            $threadsWithActivity = Thread::whereIn('id', function ($query) use ($userId) {
                    $query->select('thread_id')
                        ->from('replies')
                        ->where('user_id', $userId)
                        ->distinct();
                })
                ->whereHas('lastReply', fn($q) => $q->where('user_id', '!=', $userId))
                ->with(['lastReply.owner', 'forum'])
                ->withCount('replies')
                ->orderByDesc('last_activity_at')
                ->limit(5)
                ->get();

            return [
                'totalReps' => $replyReps + $threadReps,
                'totalNegs' => $replyNegs + $threadNegs,
                'recentRepActivity' => $recentRepActivity,
                'userThreads' => $userThreads,
                'threadsWithActivity' => $threadsWithActivity,
            ];
        });

        $totalReps = $dashboardData['totalReps'];
        $totalNegs = $dashboardData['totalNegs'];
        $repScore = $totalReps - $totalNegs;
        $recentRepActivity = collect($dashboardData['recentRepActivity']);
        $userThreads = $dashboardData['userThreads'];
        $threadsWithActivity = $dashboardData['threadsWithActivity'];

        // Game stats (already loaded with user or simple query)
        $gameStats = $user->gameStats;

        // Messages - these need to be fresh
        $unreadMessageCount = Cache::remember("user:{$userId}:unread_messages", 60, fn() => $user->unreadMessagesCount());
        $messageThreads = MessageThread::forUser($userId)
            ->latest('updated_at')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'user',
            'threadsWithActivity',
            'recentRepActivity',
            'userThreads',
            'gameStats',
            'unreadMessageCount',
            'messageThreads',
            'repScore',
            'totalReps',
            'totalNegs'
        ));
    }
}
