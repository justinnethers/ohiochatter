<?php

namespace App\Http\Controllers;

use App\Models\VbForum;
use App\Models\VbThread;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function index()
    {
        // Define forum groups
        $groups = [
            'main' => [
                'name' => 'OhioChatter Forums',
                'forums' => [6, 12, 35, 36], // Serious Business, Politics, Thread Bomber's Basement, Hall of Fame
            ],
            'hs_sports' => [
                'name' => 'HS Sports',
                'forums' => [8, 34, 10, 41], // Football, Scores and Updates, Wrestling, The Rest
            ],
            'college_pro' => [
                'name' => 'College and Pro Sports',
                'forums' => [7, 32, 42, 15, 16], // Pro Sports, Fantasy Sports, College Sports, College Football, College Basketball
            ],
        ];

        $allForumIds = collect($groups)->pluck('forums')->flatten()->all();

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
            ->whereIn('vb_forums.forumid', $allForumIds)
            ->get()
            ->keyBy('forumid');

        // Organize forums into groups maintaining order
        $groupedForums = [];
        foreach ($groups as $key => $group) {
            $groupedForums[$key] = [
                'name' => $group['name'],
                'forums' => collect($group['forums'])
                    ->map(fn($id) => $forums->get($id))
                    ->filter(),
            ];
        }

        return view('archive/index', compact('groupedForums'));
    }

    public function forum(VbForum $forum)
    {
        $threads = $forum->threads()->with(['creator.avatar'])->paginate(50);

        return view('archive/forum', compact('forum', 'threads'));
    }

    public function thread(VbThread $thread)
    {
        $thread->load('forum');
        $posts = $thread->posts()->with(['creator.avatar'])->orderBy('dateline')->paginate(25);

        return view('archive/thread', compact('posts', 'thread'));
    }
}
