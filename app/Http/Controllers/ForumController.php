<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function show(Forum $forum)
    {
        if (!auth()->check() && $forum->is_restricted) {
            return redirect('login');
        }

        $threads = Thread::where('forum_id', $forum->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(config('forum.threads_per_page'));

        return view(
            'forums.show',
            compact('forum', 'threads')
        );
    }
}
