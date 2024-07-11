<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class ThreadController extends Controller
{
    public function index()
    {
        $threads = Thread::latest('updated_at')
            ->paginate(config('forum.threads_per_page'));

        return view(
            'threads.index',
            compact('threads')
        );
    }

    public function create(Forum $forum)
    {
        return view('threads.create', [
            'forums' => Forum::where('is_active', true)->get(),
            'forum' => $forum
        ]);
    }

    public function store(StoreThreadRequest $request)
    {
        if (auth()->user()->is_banned) {
            return redirect("/threads")
                ->with('message', 'No.');
        }

        $thread = Thread::create([
            'user_id' => auth()->id(),
            'forum_id' => request('forum_id'),
            'title' => request('title'),
            'body' => request('body')
        ]);

        Cache::rememberForever(
            "thread-{$thread->id}-latest-post",
            fn () => $thread
        );

        Cache::rememberForever(
            "forum-{$thread->forum_id}-latest-post",
            fn () => $thread
        );

        Cache::forget('forums');

        if ($request->get('has_poll')) {
            $poll = Poll::create([
                'user_id' => auth()->id(),
                'thread_id' => $thread->id,
                'type' => $request->get('poll_type')
            ]);

            foreach ($request->get('options') as $option) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'label' => $option
                ]);
            }
        }

        if (request()->wantsJson()) {
            return response($thread, 201);
        }

        return redirect($thread->path())
            ->with('message', 'Your thread was successfully created.');
    }

    public function show(Forum $forum, Thread $thread)
    {
        if ($forum->slug !== $thread->forum->slug) abort(404);

        if (!auth()->check() && $forum->is_restricted) {
            return redirect("login");
        }

        $redirect = false;
        $repliesPerPage = config('forum.replies_per_page');
        $repliesCount = $thread->replyCount();

        if (auth()->check()) {

            $repliesPerPage = auth()->user()->repliesPerPage();

            if (request()->exists('newestpost')) {
                $lastView = auth()->user()->lastViewedThreadAt($thread);

                if ($lastView) {
                    $repliesSinceLastView = $thread->replies()->where('created_at', '>=', $lastView)->get();
                    $repliesSinceLastViewCount = $repliesSinceLastView->count();
                    if ($repliesSinceLastViewCount > 0) {
                        $key = $repliesSinceLastView->keys()[0];
                        $page = (int) (($repliesCount - $repliesSinceLastViewCount) / $repliesPerPage) + 1;
                        $redirect = "/forums/{$forum->slug}/{$thread->slug}/?page={$page}#reply-{$repliesSinceLastView[$key]->id}";
                    } else {
                        $page = (int) ($repliesCount / $repliesPerPage) + 1;
                        $lastReply = $thread->replies->last()->id ?? 0;
                        $redirect = "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$lastReply}";
                    }
                }

            }

            auth()->user()->read($thread);
            auth()->user()->touchActivity();
        }

        if ($redirect) {
            return redirect($redirect);
        }

        $poll = false;
        $hasVoted = false;
        $voteCount = 0;

        if ($thread->poll) {
            $poll = $thread->poll;

            foreach ($poll->pollOptions as $option) {
                foreach ($option->votes as $vote) {
                    if ($vote->user->id == auth()->id()) {
                        $hasVoted = true;
                    }
                    $voteCount++;
                }
            }
        }

        $replies = $thread->replies()->paginate($repliesPerPage);

//        if (auth()->check()) {
//            return view('threads.show', [
//                'forum' => $forum,
//                'thread' => $thread,
//                'replies' => $replies,
//                'poll' => $poll,
//                'hasVoted' => $hasVoted,
//                'voteCount' => $voteCount,
//            ]);
//        }

        return view('threads.show', [
            'forum' => $forum,
            'thread' => $thread,
            'replies' => $replies,
            'poll' => $poll,
            'hasVoted' => $hasVoted,
            'voteCount' => $voteCount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThreadRequest $request, Thread $thread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thread $thread)
    {
        //
    }
}
