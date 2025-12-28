<?php

namespace App\Actions\Threads;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Thread;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateThread
{
    public function execute(array $validated)
    {
        if (auth()->user()->is_banned) {
            throw new \Exception('User is banned from creating threads.');
        }

        return DB::transaction(function () use ($validated) {
            $thread = $this->createThread($validated);

            if ($validated['has_poll'] ?? false) {
                $this->createPoll($thread, $validated);
            }

            $this->updateCache($thread);

            // Clear listing caches after poll is created so the poll relationship is included
            app(InvalidateThreadCaches::class)->execute($thread->forum_id);

            return $thread;
        });
    }

    private function createThread(array $data): Thread
    {
        return Thread::create([
            'user_id' => auth()->id(),
            'forum_id' => $data['forum_id'],
            'title' => $data['title'],
            'body' => $data['body']
        ]);
    }

    private function updateCache(Thread $thread): void
    {
        Cache::rememberForever(
            "thread-{$thread->id}-latest-post",
            fn () => $thread
        );

        Cache::rememberForever(
            "forum-{$thread->forum_id}-latest-post",
            fn () => $thread
        );

        Cache::forget('forums');
    }

    private function createPoll(Thread $thread, array $data): void
    {
        $poll = Poll::create([
            'user_id' => auth()->id(),
            'thread_id' => $thread->id,
            'type' => $data['poll_type']
        ]);

        foreach ($data['options'] as $option) {
            PollOption::create([
                'poll_id' => $poll->id,
                'label' => $option
            ]);
        }
    }
}
