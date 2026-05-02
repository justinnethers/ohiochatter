<?php

namespace App\Http\Controllers;

use App\Models\VbForum;
use App\Models\VbThread;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function __construct(
        private SeoService $seoService
    ) {}

    /**
     * Forum IDs that are publicly accessible in the archive.
     * Forums not in this list require admin access.
     */
    private const PUBLIC_FORUM_IDS = [
        6, 12, 35, 36,      // Serious Business, Politics, Thread Bomber's Basement, Hall of Fame
        8, 34, 10, 41,      // HS Sports: Football, Scores and Updates, Wrestling, The Rest
        7, 32, 42, 15, 16,  // College and Pro Sports
    ];

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
                    ->map(fn ($id) => $forums->get($id))
                    ->filter(),
            ];
        }

        $seo = $this->seoService->forArchiveIndex();

        return view('archive/index', compact('groupedForums', 'seo'));
    }

    public function forum(Request $request, VbForum $forum)
    {
        $this->authorizeForumAccess($request, $forum->forumid);

        // Redirect to canonical URL if slug is missing or incorrect
        $canonicalKey = $forum->getRouteKey();
        $currentKey = $request->segment(3); // Get raw URL segment: /archive/forum/{this}

        if ($currentKey !== $canonicalKey) {
            return redirect('/archive/forum/'.$canonicalKey, 301);
        }

        $threads = $forum->threads()->with(['creator.avatar'])->paginate(50);
        $seo = $this->seoService->forArchiveForum($forum);

        return view('archive/forum', compact('forum', 'threads', 'seo'));
    }

    public function thread(Request $request, VbThread $thread)
    {
        $this->authorizeForumAccess($request, $thread->forumid);

        // Redirect to canonical URL if slug is missing or incorrect
        $canonicalKey = $thread->getRouteKey();
        $currentKey = $request->segment(3); // Get raw URL segment: /archive/thread/{this}

        if ($currentKey !== $canonicalKey) {
            return redirect('/archive/thread/'.$canonicalKey, 301);
        }

        $thread->load('forum');
        $posts = $thread->posts()->with(['creator.avatar'])->orderBy('dateline')->paginate(25);
        $seo = $this->seoService->forArchiveThread($thread, $posts->first());

        return view('archive/thread', compact('posts', 'thread', 'seo'));
    }

    /**
     * Redirect legacy vBulletin showthread.php URLs to the canonical archive URL.
     *
     * Legacy URL example:
     *   /forum/showthread.php?48553-2017-OC-Mock-NFL-Draft-Round-1
     * The query string after "?" carries the thread id and slug joined by a dash.
     */
    public function legacyShowThreadRedirect(Request $request)
    {
        $queryString = $request->getQueryString();

        if (empty($queryString)) {
            return redirect('/archive', 301);
        }

        // Strip the trailing "=" PHP appends when the query string has no value.
        $queryString = rtrim($queryString, '=');

        // Split into thread id and (optional) slug at the first dash.
        [$threadId, $titleSlug] = array_pad(explode('-', $queryString, 2), 2, '');

        if ($titleSlug !== '') {
            return redirect('/archive/thread/'.$threadId.'-'.$titleSlug, 301);
        }

        return redirect()->route('archive.thread', $threadId, 301);
    }

    /**
     * Abort with 404 if the forum is not public and the user is not an admin.
     */
    private function authorizeForumAccess(Request $request, int $forumId): void
    {
        if (in_array($forumId, self::PUBLIC_FORUM_IDS)) {
            return;
        }

        if (! $request->user() || ! $request->user()->is_admin) {
            abort(404);
        }
    }
}
