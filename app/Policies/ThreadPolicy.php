<?php

namespace App\Policies;

use App\Models\User;

class ThreadPolicy
{
    /**
     * Thread management is admin-only.
     *
     * Returning false for non-admins denies every ability; admins are
     * granted access here, so no per-ability methods are needed.
     */
    public function before(User $user, string $ability): bool
    {
        return $user->isAdmin();
    }
}
