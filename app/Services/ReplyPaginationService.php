<?php

namespace App\Services;

use App\Models\Reply;
use Illuminate\Support\Facades\DB;

class ReplyPaginationService
{
    /**
     * Get the position of a reply within its thread.
     * Only counts non-deleted replies with ID <= the given reply's ID.
     */
    public function getReplyPosition(Reply $reply): int
    {
        return Reply::where('thread_id', $reply->thread_id)
            ->where('id', '<=', $reply->id)
            ->count();
    }

    /**
     * Get the page number where a reply appears in thread pagination.
     */
    public function getPageForReply(Reply $reply, int $perPage): int
    {
        $position = $this->getReplyPosition($reply);

        return self::calculatePage($position, $perPage);
    }

    /**
     * Calculate page number from position and items per page.
     */
    public static function calculatePage(int $position, int $perPage): int
    {
        return (int) ceil($position / $perPage);
    }

    /**
     * Get the default replies per page for the current user or guest.
     */
    public static function getPerPage(): int
    {
        return auth()->check()
            ? auth()->user()->repliesPerPage()
            : config('forum.replies_per_page', 20);
    }

    /**
     * Get the SQL expression for calculating reply position.
     * Useful for bulk queries where you need position for multiple replies.
     * Note: Use this within the context of a query that has 'replies' table.
     */
    public static function positionSubquery(): string
    {
        return 'SELECT COUNT(*) FROM replies r2 WHERE r2.thread_id = replies.thread_id AND r2.id <= replies.id AND r2.deleted_at IS NULL';
    }

    /**
     * Get the raw DB expression for use in selectSub or similar.
     */
    public static function positionExpression(): \Illuminate\Database\Query\Expression
    {
        return DB::raw('(' . self::positionSubquery() . ')');
    }
}