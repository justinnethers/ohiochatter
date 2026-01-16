<?php

namespace App\Observers;

use App\Models\Thread;

class ThreadObserver
{
    public function created(Thread $thread)
    {
        $thread->update(['last_activity_at' => $thread->created_at]);
        $thread->owner?->increment('post_count');
    }

    public function deleted(Thread $thread)
    {
        $thread->owner?->decrement('post_count');
    }

    public function restored(Thread $thread)
    {
        $thread->owner?->increment('post_count');
    }
}
