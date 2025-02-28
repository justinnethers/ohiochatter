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
use Illuminate\Support\Facades\Cache;

class ThreadController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);

        if ($page == 1) {
            $threads = Cache::remember('all_threads_base_query', now()->addDay(), function() {
                return Thread::query()
                    ->with(['owner', 'forum', 'poll'])
                    ->orderBy('last_activity_at', 'desc')
                    ->get();
            });

            // Create a fresh paginator from the cached collection
            $threads = new \Illuminate\Pagination\LengthAwarePaginator(
                $threads->forPage(1, config('forum.threads_per_page')),
                $threads->count(),
                config('forum.threads_per_page'),
                1
            );
        } else {
            $threads = Thread::query()
                ->with(['owner', 'forum', 'poll'])
                ->orderBy('last_activity_at', 'desc')
                ->paginate(config('forum.threads_per_page'));
        }

        $threads->withPath(request()->url());
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
