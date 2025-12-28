<?php

namespace App\Http\Controllers;

use App\Models\Neg;
use App\Models\Rep;
use App\Models\Reply;
use App\Models\Thread;
use Cmgmyr\Messenger\Models\Thread as MessageThread;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $userId = $user->id;

        // Track dashboard visits
        $user->increment('dashboard_visits');

        // Cache everything expensive for 5 minutes
        $dashboardData = Cache::remember("user:{$userId}:dashboard", 300, function () use ($userId) {
            // Rep counts using JOINs
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

        $totalReps = $dashboardData['totalReps'];
        $totalNegs = $dashboardData['totalNegs'];
        $repScore = $totalReps - $totalNegs;

        // Recent rep activity - single query with JOINs to get thread info
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
                DB::raw('(SELECT COUNT(*) FROM replies r2 WHERE r2.thread_id = replies.thread_id AND r2.id <= replies.id) as reply_position')
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
                DB::raw('(SELECT COUNT(*) FROM replies r2 WHERE r2.thread_id = replies.thread_id AND r2.id <= replies.id) as reply_position')
            )
            ->orderByDesc('negs.created_at')
            ->limit(5)
            ->get()
            ->map(fn($n) => ['type' => 'neg', 'username' => $n->username, 'thread_title' => $n->thread_title, 'thread_slug' => $n->thread_slug, 'forum_slug' => $n->forum_slug, 'reply_id' => $n->reply_id, 'reply_position' => $n->reply_position, 'created_at' => $n->created_at]);

        $recentRepActivity = $reps->merge($negs)
            ->sortByDesc('created_at')
            ->take(5);

        // User's recent threads
        $userThreads = Thread::where('user_id', $userId)
            ->with('forum')
            ->withCount('replies')
            ->latest()
            ->limit(5)
            ->get();

        // Threads with new activity from others - single efficient query
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
            LIMIT 5
        ", [$userId, $userId]);

        $threadsWithActivity = Thread::whereIn('id', collect($threadIdsWithActivity)->pluck('id'))
            ->with(['lastReply.owner', 'forum'])
            ->withCount('replies')
            ->get()
            ->sortByDesc(fn($t) => $t->lastReply->created_at);

        // Game stats
        $gameStats = $user->gameStats;

        // Messages
        $unreadMessageCount = $user->unreadMessagesCount();
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
