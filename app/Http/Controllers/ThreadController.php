<?php

namespace App\Http\Controllers;

use App\Actions\Threads\CreateThread;
use App\Actions\Threads\FetchThreadDetails;
use App\Actions\Threads\HandleThreadNavigation;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function index()
    {
        $threads = Thread::query()
            ->select('threads.*')
            ->orderByRaw('GREATEST(
                COALESCE((SELECT MAX(created_at) FROM replies
                 WHERE thread_id = threads.id AND deleted_at IS NULL), threads.created_at),
                threads.created_at,
                threads.updated_at
            ) DESC')
            ->withCount('replies')
            ->paginate(config('forum.threads_per_page'));

        return view('threads.index', compact('threads'));
    }

    public function create(Forum $forum)
    {
        return view('threads.create', [
            'forums' => Forum::where('is_active', true)->get(),
            'forum' => $forum
        ]);
    }

    public function store(StoreThreadRequest $request, CreateThread $action)
    {
        $thread = $action->execute($request->validated());

        if ($request->wantsJson()) {
            return response($thread, 201);
        }

        return redirect($thread->path())
            ->with('message', 'Your thread was successfully created.');
    }

    public function show(
        Request $request,
        Forum $forum,
        Thread $thread,
        FetchThreadDetails $detailsFetcher,
        HandleThreadNavigation $navigationHandler
    ) {
        if ($forum->slug !== $thread->forum->slug) {
            abort(404);
        }

        if (!auth()->check() && $forum->is_restricted) {
            return redirect('login');
        }

        $redirect = $navigationHandler->execute($request, $forum, $thread);

        if ($redirect) {
            return redirect($redirect);
        }

        return view('threads.show', $detailsFetcher->execute($thread));
    }

    public function edit(Thread $thread)
    {
        $this->authorize('update', $thread);

        return view('threads.edit', compact('thread'));
    }

    public function update(UpdateThreadRequest $request, Thread $thread)
    {
        $this->authorize('update', $thread);

        $thread->update($request->validated());

        return redirect($thread->path())
            ->with('message', 'Thread updated successfully.');
    }

    public function destroy(Thread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        return redirect('/threads')
            ->with('message', 'Thread deleted successfully.');
    }
}
