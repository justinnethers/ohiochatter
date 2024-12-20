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

        auth()->user()->read($thread);
        auth()->user()->touchActivity();

        if (!$request->exists('newestpost')) {
            return null;
        }

        return $this->handleNewestPostNavigation($forum, $thread);
    }

    private function handleNewestPostNavigation(Forum $forum, Thread $thread): ?string
    {
        $lastView = auth()->user()->lastViewedThreadAt($thread);

        if (!$lastView) {
            return null;
        }

        $repliesPerPage = auth()->user()->repliesPerPage();
        $repliesCount = $thread->replyCount();

        $repliesSinceLastView = $thread->replies()
            ->where('created_at', '>=', $lastView)
            ->get();

        $repliesSinceLastViewCount = $repliesSinceLastView->count();

        if ($repliesSinceLastViewCount > 0) {
            $key = $repliesSinceLastView->keys()[0];
            $page = (int) (($repliesCount - $repliesSinceLastViewCount) / $repliesPerPage) + 1;

            return "/forums/{$forum->slug}/{$thread->slug}/?page={$page}#reply-{$repliesSinceLastView[$key]->id}";
        }

        $page = (int) ($repliesCount / $repliesPerPage) + 1;
        $lastReply = $thread->replies->last()->id ?? 0;

        return "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
    }
}
