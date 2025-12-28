<?php

namespace App\Observers;

use App\Models\Thread;

class ThreadObserver
{
    public function created(Thread $thread)
    {
        $thread->update(['last_activity_at' => $thread->created_at]);
    }
}
