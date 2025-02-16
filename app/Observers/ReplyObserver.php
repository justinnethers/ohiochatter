<?php

namespace App\Observers;

use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        $reply->thread()->update([
            'last_activity_at' => $reply->created_at,
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        $this->regenerateThreadsCache($reply->thread->forum_id);
    }

    public function deleted(Reply $reply)
    {
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        $this->regenerateThreadsCache($reply->thread->forum_id);
    }

    public function restored(Reply $reply)
    {
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        $this->regenerateThreadsCache($reply->thread->forum_id);
    }

    private function regenerateThreadsCache($forumId)
    {
        // Cache all threads
        $allThreads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        Cache::put('all_threads', $allThreads, now()->addDay());

        // Cache forum-specific threads
        $forumThreads = Thread::query()
            ->with(['owner', 'forum', 'poll'])
            ->where('forum_id', $forumId)
            ->orderBy('last_activity_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        Cache::put("forum_{$forumId}_threads", $forumThreads, now()->addDay());
    }
}
