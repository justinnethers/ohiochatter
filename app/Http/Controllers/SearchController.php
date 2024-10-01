<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index');
    }

    public function show()
    {
        $search = request('search');

//        $threads = Thread::search($search)->get();
        $posts = Reply::search($search)->get();
        $users = User::search($search)->get();

//        dump($threads);
//        dump($posts);
//        dump($users);

        return view('search.show', compact('users'));
    }
}
