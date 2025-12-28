<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Illuminate\Support\Facades\Cache;

class InvalidateThreadCaches
{
    public function execute(int $forumId = null)
    {
        // Clear all threads caches (both auth and guest)
        Cache::forget('all_threads_base_query');
        Cache::forget('all_threads_base_query_guest');

        // If forum ID provided, clear that forum's paginated caches
        if ($forumId) {
            // Clear first 10 pages of forum cache (most commonly accessed)
            for ($page = 1; $page <= 10; $page++) {
                Cache::forget("forum_{$forumId}_threads_page_{$page}");
            }
        }
    }
}
