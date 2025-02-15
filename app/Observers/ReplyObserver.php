<?php

namespace App\Observers;

use App\Models\Reply;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        $reply->thread()->update([
            'last_activity_at' => $reply->created_at,
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);
    }

    public function deleted(Reply $reply)
    {
        // Only update replies_count, not last_activity_at
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);
    }

    public function restored(Reply $reply)
    {
        // When a soft-deleted reply is restored
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);
    }
}
