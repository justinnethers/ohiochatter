<?php

namespace App\Actions\Threads;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class HandleThreadNavigation
{
    public function execute(Request $request, Forum $forum, Thread $thread): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        if (!$request->exists('newestpost')) {
            return null;
        }

        return $this->handleNewestPostNavigation($forum, $thread);
    }

    private function handleNewestPostNavigation(Forum $forum, Thread $thread): ?string
    {
        $lastView = auth()->user()->lastViewedThreadAt($thread);

        auth()->user()->read($thread);

        if (!$lastView) {
            return null;
        }

        $repliesPerPage = auth()->user()->repliesPerPage();
        $repliesCount = $thread->replyCount();

        $repliesSinceLastView = $thread->replies()
            ->where('created_at', '>=', $lastView)
            ->get();

        if ($repliesSinceLastView->count() > 0) {
            $page = ceil(($repliesCount - $repliesSinceLastView->count()) / $repliesPerPage);

            return "/forums/{$forum->slug}/{$thread->slug}/?page={$page}#reply-{$repliesSinceLastView->first()->id}";
        }

        $page = ceil($repliesCount / $repliesPerPage);
        $lastReply = $thread->replies->last()->id ?? 0;

        return "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
    }
}
