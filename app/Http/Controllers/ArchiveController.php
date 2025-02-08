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
        $forums = VbForum::query()
            ->select('vb_forums.*', 'latest_thread.title as latest_thread_title',
                'latest_thread.threadid as latest_thread_id',
                'latest_thread.lastposter as latest_thread_poster',
                'latest_thread.lastpost as latest_thread_lastpost')
            ->leftJoin(DB::raw('(
                SELECT t.forumid, t.title, t.threadid, t.lastposter, t.lastpost
                FROM vb_threads t
                INNER JOIN (
                    SELECT forumid, MAX(lastpost) as max_lastpost
                    FROM vb_threads
                    WHERE visible = 1
                    GROUP BY forumid
                ) latest ON t.forumid = latest.forumid AND t.lastpost = latest.max_lastpost
                WHERE t.visible = 1
            ) as latest_thread'), 'vb_forums.forumid', '=', 'latest_thread.forumid')
            ->where('vb_forums.parentid', '>', 0)
            ->where('vb_forums.displayorder', '>', 0)
            ->orderBy('vb_forums.displayorder')
            ->get();

        return view('archive/index', compact('forums'));
    }

    public function forum(VbForum $forum, Request $request)
    {
        $threads = $forum->threads()->with('creator')->paginate(50);
        return view('archive/forum', compact('threads'));
    }

    public function thread(VbForum $forum, VbThread $thread, Request $request)
    {
        $posts = $thread->posts()->orderBy('dateline')->paginate(25);
        return view('archive/thread', compact('posts', 'thread'));
    }
}
