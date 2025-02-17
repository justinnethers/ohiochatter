<?php

namespace App\Observers;

use App\Actions\Threads\CacheThreads;
use App\Models\Thread;

class ThreadObserver
{
    public function created(Thread $thread)
    {
        $thread->update(['last_activity_at' => $thread->created_at]);

        app(CacheThreads::class)->execute($thread->forum_id);
    }
}
