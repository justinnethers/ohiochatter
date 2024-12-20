<?php

namespace App\Actions\Threads;

use App\Models\Thread;

class FetchThreadDetails
{
    public function execute(Thread $thread): array
    {
        $repliesPerPage = $this->getRepliesPerPage();

        return [
            'forum' => $thread->forum,
            'thread' => $thread,
            'replies' => $thread->replies()->paginate($repliesPerPage),
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
