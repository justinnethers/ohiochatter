<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DeleteThreadAction
{
    public function execute(Thread $thread): bool
    {
        if (!Auth::user()?->is_admin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $thread->delete();

        return true;
    }
}
