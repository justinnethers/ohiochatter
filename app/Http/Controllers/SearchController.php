<?php

namespace App\Http\Controllers;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', storage_path('logs/search-errors.log'));

use App\Models\Thread;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected array $searchableModels = [
        Thread::class => 'Threads',
        Reply::class => 'Posts',
        User::class => 'Users'
    ];

    public function index()
    {
        return view('search.index');
    }

    public function show(Request $request)
    {
        $query = $request->input('q') ?? $request->query('query');
        if (empty($query)) {
            return redirect()->route('search.index');
        }

        $results = [
            'Threads' => Thread::search($query)->paginate(5),
//            'Posts' => Reply::search($query)
//                ->query(function ($builder) {
//                    return $builder->select('id', 'body', 'thread_id', 'user_id')
//                        ->with(['owner:id,username', 'thread:id,title,slug']);
//                })
//                ->take(10)
//                ->get()
        ];

        return view('search.show', [
            'query' => $query,
            'results' => $results
        ]);
    }

    public function addSearchableModel(string $modelClass, string $label)
    {
        $this->searchableModels[$modelClass] = $label;
    }
}
