<?php

namespace App\Services;

use App\Models\User;
use App\Models\VbForum;
use App\Models\VbPost;
use App\Models\VbThread;
use App\Models\VbUser;

class LegacyVbulletinUrlResolver
{
    /**
     * Posts per page on the archive thread view (see ArchiveController::thread).
     */
    public const ARCHIVE_POSTS_PER_PAGE = 25;

    /**
     * Posts per page on a vBulletin showthread.php page.
     *
     * ASSUMPTION: 15 is vBulletin's default "maxposts" setting. The real
     * setting on the old OhioChatter site is unknown, so legacy deep links
     * may land one archive page off until this is confirmed.
     */
    public const VB_POSTS_PER_PAGE = 15;

    /**
     * Posts per page on a vBulletin lite-archive (archive/index.php/t-N-p-M.html) page.
     *
     * ASSUMPTION: 250 is the vBulletin lite-archive default. The real value
     * on the old site is unknown.
     */
    public const VB_LITE_ARCHIVE_POSTS_PER_PAGE = 250;

    /**
     * Canonical archive URL for a thread, or null when it has no visible target.
     *
     * The vBulletin page is translated to the archive page holding the first
     * post of that vBulletin page, clamped so it never lands on an empty page.
     */
    public function threadUrl(int $threadId, int $vbPage, int $vbPostsPerPage, ?User $viewer): ?string
    {
        $thread = $this->visibleThread($threadId, $viewer);

        if (! $thread) {
            return null;
        }

        $vbPage = min(max(1, $vbPage), 1_000_000);
        $page = intdiv(($vbPage - 1) * $vbPostsPerPage, self::ARCHIVE_POSTS_PER_PAGE) + 1;

        $lastPage = max(1, (int) ceil($thread->posts()->count() / self::ARCHIVE_POSTS_PER_PAGE));

        return $this->archiveThreadUrl($thread, min($page, $lastPage));
    }

    /**
     * Canonical archive URL for the page of the thread that contains the post.
     */
    public function postUrl(int $postId, ?User $viewer): ?string
    {
        $post = VbPost::query()->find($postId);

        if (! $post) {
            return null;
        }

        $thread = $this->visibleThread((int) $post->threadid, $viewer);

        if (! $thread) {
            return null;
        }

        $position = VbPost::query()
            ->where('threadid', $post->threadid)
            ->where(function ($query) use ($post) {
                $query->where('dateline', '<', $post->dateline)
                    ->orWhere(function ($query) use ($post) {
                        $query->where('dateline', $post->dateline)
                            ->where('postid', '<', $post->postid);
                    });
            })
            ->count();

        return $this->archiveThreadUrl($thread, intdiv($position, self::ARCHIVE_POSTS_PER_PAGE) + 1);
    }

    /**
     * Canonical archive URL for a forum, or null when it has no visible target.
     *
     * Always page 1: the archive listing is 50 threads per page ordered by
     * last post and includes hidden threads, so vBulletin forum pages cannot
     * be mapped onto it reliably.
     */
    public function forumUrl(int $forumId, ?User $viewer): ?string
    {
        if (! $this->canViewForum($forumId, $viewer)) {
            return null;
        }

        $forum = VbForum::query()->find($forumId);

        return $forum ? route('archive.forum', $forum) : null;
    }

    /**
     * Profile URL for the current user whose username matches the vBulletin
     * user, or null when the vBulletin user has no current account.
     */
    public function memberUrl(int $vbUserId): ?string
    {
        $vbUser = VbUser::query()->find($vbUserId);

        if (! $vbUser) {
            return null;
        }

        $user = User::query()
            ->where('username', $vbUser->username)
            ->notFlaggedAsSpam()
            ->first();

        return $user ? route('profile.show', ['user' => $user->username]) : null;
    }

    private function visibleThread(int $threadId, ?User $viewer): ?VbThread
    {
        $thread = VbThread::query()->find($threadId);

        if (! $thread || (int) $thread->visible !== 1) {
            return null;
        }

        return $this->canViewForum((int) $thread->forumid, $viewer) ? $thread : null;
    }

    private function canViewForum(int $forumId, ?User $viewer): bool
    {
        return VbForum::isPublicForumId($forumId) || (bool) $viewer?->is_admin;
    }

    private function archiveThreadUrl(VbThread $thread, int $page): string
    {
        if ($page > 1) {
            return route('archive.thread', ['thread' => $thread, 'page' => $page]);
        }

        return route('archive.thread', $thread);
    }
}
