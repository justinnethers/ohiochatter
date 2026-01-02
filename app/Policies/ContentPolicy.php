<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    /**
     * Admins can do anything with content.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine if user can view the content.
     */
    public function view(?User $user, Content $content): bool
    {
        // Published content is viewable by anyone
        if ($content->published_at !== null) {
            return true;
        }

        // Unpublished content only viewable by author
        return $user && $user->id === $content->user_id;
    }

    /**
     * Determine if user can update the content.
     * Only original authors can suggest edits (non-admins).
     */
    public function update(User $user, Content $content): bool
    {
        return $user->id === $content->user_id;
    }

    /**
     * Determine if user can delete the content.
     */
    public function delete(User $user, Content $content): bool
    {
        return false; // Only admins (handled by before())
    }

    /**
     * Determine if user can review revisions for this content.
     */
    public function reviewRevisions(User $user, Content $content): bool
    {
        return false; // Only admins (handled by before())
    }
}
