<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ToggleLockAction
{
    /**
     * Execute the action to toggle thread lock status.
     *
     * @param Thread $thread
     * @param mixed $user
     * @return Thread
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function execute(Thread $thread)
    {
        if (!Auth::user()?->is_admin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $thread->update([
            'locked' => !$thread->locked
        ]);

        return $thread->fresh();
    }
}
