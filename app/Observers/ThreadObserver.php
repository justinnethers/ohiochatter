<?php

namespace App\Observers;

use App\Models\Thread;

class ThreadObserver
{
    public function creating(Thread $thread)
    {
        $thread->last_activity_at = $thread->created_at;
    }
}
