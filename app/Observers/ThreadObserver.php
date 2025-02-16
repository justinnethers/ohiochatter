<?php

namespace App\Observers;

use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class ThreadObserver
{
    public function created(Thread $thread)
    {
        $thread->update(['last_activity_at' => $thread->created_at]);

        // Cache page 1 of all threads
        $allThreads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        Cache::put('all_threads_page_1', $allThreads, now()->addDay());

        // Cache page 1 of forum-specific threads
        $forumThreads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->where('forum_id', $thread->forum_id)
            ->orderBy('last_activity_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        Cache::put("forum_{$thread->forum_id}_threads_page_1", $forumThreads, now()->addDay());
    }
}
