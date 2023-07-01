<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Forum;
use App\Models\Thread;

class ThreadController extends Controller
{
    // show a paginated list of all threads
    public function index()
    {
        $forums = Forum::all();
        $threads = Thread::with(['forum'])->latest('updated_at')->paginate(config('forum.threads_per_page'));

        $forums = $forums->reject(function (Forum $forum) {
            return !$forum->is_active;
        });
        return view('threads.index', compact('forums', 'threads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThreadRequest $request)
    {
        //
    }

    public function show(Forum $forum, Thread $thread)
    {
        if ($forum->slug !== $thread->forum->slug) abort(404);

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
                        $redirect = "/forums/{$forum->slug}/{$thread->slug}?page={$page}#reply-{$thread->replies->last()->id}";
                    }
                }

            }

            auth()->user()->read($thread);
            auth()->user()->touchActivity();
        } else {
            // if unauthed user attempts to view restricted forum thread, redirect to login
            if ($forum->is_restricted) {
                return redirect("login");
            }
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
