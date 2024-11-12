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
     * Update the specified resource in storage.
     */
    public function update(UpdateReplyRequest $request, Reply $reply)
    {
        return $reply->update([
            'body' => $request->body,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        return $reply->delete();
    }
}
