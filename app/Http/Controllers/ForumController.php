<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class ForumController extends Controller
{

    public function index()
    {
        $forums = Forum::all();

        $forums = $forums->reject(function (Forum $forum) {
            return !$forum->is_active;
        });
        return view('forums.index', compact('forums'));
    }

    // show the forum and a paginated list of its threads
    public function show(Forum $forum)
    {
        $threads = Thread::where('forum_id', $forum->id)->orderBy('updated_at', 'desc')->paginate(50);
        return view('forums.show', compact('forum', 'threads'));
    }
}
