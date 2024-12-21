<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function show(Forum $forum)
    {
        if (!auth()->check() && $forum->is_restricted) {
            return redirect('login');
        }

        $threads = Thread::with(['owner', 'forum', 'poll'])
            ->where('forum_id', $forum->id)
            ->select('threads.*')
            ->leftJoin('replies', function($join) {
                $join->on('threads.id', '=', 'replies.thread_id')
                    ->whereNull('replies.deleted_at');
            })
            ->groupBy('threads.id')
            ->orderByRaw('GREATEST(COALESCE(MAX(replies.created_at), threads.created_at), threads.updated_at) DESC')
            ->paginate(config('forum.threads_per_page'));

        return view('forums.show', compact('forum', 'threads'));
    }
}
