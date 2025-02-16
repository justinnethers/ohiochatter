<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class ForumController extends Controller
{
    public function show(Forum $forum)
    {
        if (!auth()->check() && $forum->is_restricted) {
            return redirect('login');
        }

        $page = request()->get('page', 1);
        $threads = Cache::remember("forum_{$forum->id}_threads_page_{$page}", now()->addDay(), function() use ($forum) {
            return Thread::query()
                ->with(['owner', 'forum', 'poll'])
                ->where('forum_id', $forum->id)
                ->orderBy('last_activity_at', 'desc')
                ->paginate(config('forum.threads_per_page'));
        });

        return view('forums.show', compact('forum', 'threads'));
    }
}
