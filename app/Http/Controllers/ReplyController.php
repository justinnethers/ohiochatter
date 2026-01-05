<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Services\MentionService;
use Illuminate\Http\RedirectResponse;

class ReplyController extends Controller
{
    public function __construct(
        protected MentionService $mentionService
    ) {}
    /**
     * Store a newly created reply in storage.
     *
     * @param  Forum  $forum
     * @param  Thread $thread
     * @param  StoreReplyRequest $request
     * @return RedirectResponse
     */
    public function store(Forum $forum, Thread $thread, StoreReplyRequest $request): RedirectResponse
    {
        $reply = $thread->addReply([
            'body'    => $request->body,
            'user_id' => auth()->id(),
        ])->load('owner');

        // Process mentions in the reply body
        $this->mentionService->processMentions($request->body, $reply, auth()->user());

        // Calculate the page number where the new reply is located.
        // Using ceil() ensures that if the number of replies exactly fills a page,
        // the reply still shows on the correct page.
        $perPage    = auth()->user()->repliesPerPage();
        $replyCount = $thread->replyCount();
        $page       = (int) ceil($replyCount / $perPage);

        return redirect($thread->path("?page={$page}#reply-{$reply->id}"));
    }

    /**
     * Remove the specified reply from storage.
     *
     * @param  Reply $reply
     * @return bool
     */
    public function destroy(Reply $reply): bool
    {
        return $reply->delete();
    }
}
