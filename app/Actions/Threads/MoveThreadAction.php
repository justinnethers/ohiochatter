<?php

namespace App\Actions\Threads;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MoveThreadAction
{
    public function __construct(
        private InvalidateThreadCaches $invalidateCaches
    ) {}

    public function execute(Thread $thread, int $forumId): Thread
    {
        if (!Auth::user()?->is_admin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($thread->forum_id === $forumId) {
            throw new \InvalidArgumentException('Thread is already in this forum.');
        }

        $oldForumId = $thread->forum_id;
        $forum = Forum::findOrFail($forumId);

        $thread->update([
            'forum_id' => $forum->id
        ]);

        // Clear caches for both old and new forums
        $this->invalidateCaches->execute($oldForumId);
        $this->invalidateCaches->execute($forum->id);

        return $thread->fresh();
    }
}
