<?php

namespace App\Observers;

use App\Actions\Threads\InvalidateThreadCaches;
use App\Models\Reply;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        $reply->thread()->update([
            'last_activity_at' => $reply->created_at,
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        app(InvalidateThreadCaches::class)->execute($reply->thread->forum_id);
    }

    public function deleted(Reply $reply)
    {
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        app(InvalidateThreadCaches::class)->execute($reply->thread->forum_id);
    }

    public function restored(Reply $reply)
    {
        $reply->thread()->update([
            'replies_count' => $reply->thread->replies()->whereNull('deleted_at')->count()
        ]);

        app(InvalidateThreadCaches::class)->execute($reply->thread->forum_id);
    }
}
