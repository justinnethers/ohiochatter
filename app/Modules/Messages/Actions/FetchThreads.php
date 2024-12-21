<?php

namespace App\Modules\Messages\Actions;

use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Pagination\LengthAwarePaginator;

class FetchThreads
{
    public function execute(int $userId, bool $unreadOnly = false): LengthAwarePaginator
    {
        $query = $unreadOnly
            ? Thread::forUserWithNewMessages($userId)
            : Thread::forUser($userId);

        return $query->latest()->paginate(20);
    }
}
