<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Http\Requests\UpdateReplyRequest;
use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Forum $forum, Thread $thread, StoreReplyRequest $request)
    {
        $reply = $thread->addReply([
            'body' => $request->body,
            'user_id' => auth()->id()
        ])->load('owner');

        $page = (int) ($thread->replyCount() / auth()->user()->repliesPerPage()) + 1;

        return redirect($thread->path('?page='.$page. '#reply-'.$reply->id));
    }

    /**
     * Display the specified resource.
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        //
    }
}
