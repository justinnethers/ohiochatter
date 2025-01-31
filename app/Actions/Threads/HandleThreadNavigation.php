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
        auth()->user()->touchActivity();

        if (!$lastView) {
            return null;
        }

        $repliesPerPage = auth()->user()->repliesPerPage();
        $repliesCount = $thread->replyCount();

        $repliesSinceLastView = $thread->replies()
            ->where('created_at', '>=', $lastView)
            ->get();

        if ($repliesSinceLastView->count() > 0) {
            $page = (int) (($repliesCount - $repliesSinceLastView->count()) / $repliesPerPage) + 1;

            return "/forums/{$forum->slug}/{$thread->slug}/?page={$page}#reply-{$repliesSinceLastView->first()->id}";
        }

        $page = (int) ($repliesCount / $repliesPerPage) + 1;
        $lastReply = $thread->replies->last()->id ?? 0;

        return "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
    }
}
