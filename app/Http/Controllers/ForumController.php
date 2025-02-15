<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;

class ForumController extends Controller
{
    public function show(Forum $forum)
    {
        if (!auth()->check() && $forum->is_restricted) {
            return redirect('login');
        }

        $threads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->where('forum_id', $forum->id)
            ->orderBy('last_activity_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        return view('forums.show', compact('forum', 'threads'));
    }
}
