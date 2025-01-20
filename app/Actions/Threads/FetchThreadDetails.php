<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FetchThreadDetails
{
    public function execute(Thread $thread): array
    {
        $repliesPerPage = $this->getRepliesPerPage();

        $thread->load(['reps.user', 'negs.user']);

        $replies = $thread->replies()
            ->with(['owner', 'reps.user', 'negs.user'])
            ->paginate($repliesPerPage);

        // Cache user's view status
//        $lastView = auth()->check() ?
//            Cache::remember("user-".auth()->id()."-thread-{$thread->id}-view", 3600, function() use ($thread) {
//                return DB::table('threads_users_views')
//                    ->where('user_id', auth()->id())
//                    ->where('thread_id', $thread->id)
//                    ->first();
//            }) : null;

        return [
            'forum' => $thread->forum,
            'thread' => $thread,
            'replies' => $replies,
//            'lastView' => $lastView,
            ...$this->getPollDetails($thread)
        ];
    }

    private function getRepliesPerPage(): int
    {
        return auth()->check()
            ? auth()->user()->repliesPerPage()
            : config('forum.replies_per_page');
    }

    private function getPollDetails(Thread $thread): array
    {
        if (!$thread->poll) {
            return [
                'poll' => false,
                'hasVoted' => false,
                'voteCount' => 0
            ];
        }

        $voteCount = 0;
        $hasVoted = false;

        foreach ($thread->poll->pollOptions as $option) {
            foreach ($option->votes as $vote) {
                if ($vote->user->id === auth()->id()) {
                    $hasVoted = true;
                }
                $voteCount++;
            }
        }

        return [
            'poll' => $thread->poll,
            'hasVoted' => $hasVoted,
            'voteCount' => $voteCount
        ];
    }
}
