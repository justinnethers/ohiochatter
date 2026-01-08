<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Reply;
use App\Models\User;
use App\Services\ReplyPaginationService;
use App\Services\SeoService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private SeoService $seoService
    ) {}

    public function show(Request $request)
    {
        $query = $request->input('q') ?? $request->query('query');

        if (empty($query)) {
            return redirect()->route('home');
        }

        $likeQuery = '%' . $query . '%';

        // Search threads - exclude restricted forums, paginate at DB level
        $threads = Thread::where(function ($q) use ($likeQuery) {
                $q->where('title', 'like', $likeQuery)
                  ->orWhere('body', 'like', $likeQuery);
            })
            ->whereHas('forum', fn($q) => $q->where('is_restricted', false))
            ->orderByDesc('last_activity_at')
            ->paginate(5, ['*'], 'thread_page');

        // Search posts - exclude restricted forums, paginate at DB level
        // Include position for direct linking to the correct page
        // Search searchable_body which excludes quoted content
        $posts = Reply::where('searchable_body', 'like', $likeQuery)
            ->whereHas('thread.forum', fn($q) => $q->where('is_restricted', false))
            ->with(['thread.forum', 'owner'])
            ->select('replies.*')
            ->selectSub(ReplyPaginationService::positionSubquery(), 'position')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'post_page');

        // Search users
        $users = User::where('username', 'like', $likeQuery)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'user_page');

        $totalResults = $threads->total() + $posts->total() + $users->total();
        $seo = $this->seoService->forSearch($query, $totalResults);

        return view('search.show', [
            'query' => $query,
            'threads' => $threads,
            'posts' => $posts,
            'users' => $users,
            'seo' => $seo,
        ]);
    }
}
