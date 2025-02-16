<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class CacheThreads
{
    public function execute(int $forumId = null)
    {
        // Cache base collection for all threads
        $allThreads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->orderBy('last_activity_at', 'desc')
            ->get();

        Cache::put('all_threads_base_query', $allThreads, now()->addDay());

        // If forum ID provided, cache that forum's threads too
        if ($forumId) {
            $forumThreads = Thread::query()
                ->with(['owner', 'forum', 'poll'])
                ->where('forum_id', $forumId)
                ->orderBy('last_activity_at', 'desc')
                ->get();

            Cache::put("forum_{$forumId}_base_query", $forumThreads, now()->addDay());
        }
    }
}
