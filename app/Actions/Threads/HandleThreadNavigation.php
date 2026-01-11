<?php

namespace App\Actions\Threads;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class HandleThreadNavigation
{
    public function execute(Request $request, Forum $forum, Thread $thread): ?string
    {
        // If the request doesn't have the newestpost parameter, return null
        if (!$request->exists('newestpost')) {
            return null;
        }

        // If user is logged in, handle navigation with their view history
        if (auth()->check()) {
            return $this->handleNewestPostNavigationForLoggedInUser($forum, $thread);
        }

        // If user is not logged in, take them to the last post
        return $this->handleLastPostNavigation($forum, $thread);
    }

    private function handleNewestPostNavigationForLoggedInUser(Forum $forum, Thread $thread): ?string
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
            $firstUnreadPosition = $repliesCount - $repliesSinceLastView->count() + 1;
            $page = (int) ceil($firstUnreadPosition / $repliesPerPage);

            return "/forums/{$forum->slug}/{$thread->slug}/?page={$page}#reply-{$repliesSinceLastView->first()->id}";
        }

        $page = ceil($repliesCount / $repliesPerPage);
        $lastReply = $thread->replies->last()->id ?? 0;

        return "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
    }

    private function handleLastPostNavigation(Forum $forum, Thread $thread): string
    {
        // For non-logged in users, use a default value for replies per page
        // You might want to adjust this to match your application's default setting
        $repliesPerPage = config('forum.replies_per_page', 15);
        $repliesCount = $thread->replyCount();

        $page = ceil($repliesCount / $repliesPerPage);
        $lastReply = $thread->replies->last()->id ?? 0;

        return "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
    }
}
