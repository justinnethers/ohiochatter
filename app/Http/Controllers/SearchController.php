<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function show(Request $request)
    {
        $query = $request->input('q') ?? $request->query('query');


        if (empty($query)) {
            return redirect()->route('search.index');
        }

        $page = [
            'threads' => $request->get('thread_page', 1),
            'posts' => $request->get('post_page', 1),
            'users' => $request->get('user_page', 1)
        ];



        $perPage = 10;

        $threads = Thread::search($query)->get();
        $posts = Reply::search($query)->get();
        $users = User::search($query)->get();

        $result = [
            'query' => $query,
            'threads' => new LengthAwarePaginator(
                $threads->forPage($page['threads'], 5),
                $threads->count(),
                5,
                $page['threads'],
                ['path' => request()->url(), 'pageName' => 'thread_page']
            ),
            'posts' => new LengthAwarePaginator(
                $posts->forPage($page['posts'], $perPage),
                $posts->count(),
                $perPage,
                $page['posts'],
                ['path' => request()->url(), 'pageName' => 'post_page']
            ),
            'users' => new LengthAwarePaginator(
                $users->forPage($page['users'], $perPage),
                $users->count(),
                $perPage,
                $page['users'],
                ['path' => request()->url(), 'pageName' => 'user_page']
            )
        ];

        return view('search.show', $result);
    }
}
