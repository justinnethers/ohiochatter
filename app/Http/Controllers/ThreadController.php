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
        $threads = Thread::with(['forum'])->orderBy('updated_at', 'desc')->paginate(50);

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

        return view('threads.show', [
            'forum' => $forum,
            'thread' => $thread,
            'replies' => $thread->replies()->paginate(25),
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
