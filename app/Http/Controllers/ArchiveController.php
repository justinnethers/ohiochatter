<?php

namespace App\Http\Controllers;

use App\Models\VbCustomAvatar;
use App\Models\VbForum;
use App\Models\VbThread;
use App\Models\VbUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index()
    {
        $threads = VbForum::where('parentid', '>', 0)->orderBy('displayorder')->get();
//        dd($users);
//dd();
        return view('archive/index', compact('threads'));
    }

    public function forum(VbForum $forum, Request $request)
    {
        $threads = $forum->threads()->with('creator')->paginate(50);

        return view('archive/forum', compact('threads'));
    }

    public function thread(VbForum $forum, VbThread $thread, Request $request)
    {
//        dd($thread->posts()->paginate(50));
        $posts = $thread->posts()->orderBy('dateline')->paginate(25);
        return view('archive/thread', compact('posts', 'thread'));

    }


}
