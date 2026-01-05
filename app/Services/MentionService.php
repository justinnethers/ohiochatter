<?php

namespace App\Services;

use App\Models\Mention;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Database\Eloquent\Model;

class MentionService
{
    /**
     * Parse mentions from HTML body and create notifications.
     */
    public function processMentions(string $body, Model $mentionable, User $author): void
    {
        $mentionedUserIds = $this->extractMentionedUserIds($body);

        // Filter out self-mentions
        $mentionedUserIds = array_filter($mentionedUserIds, fn($id) => $id !== $author->id);

        // Remove duplicates
        $mentionedUserIds = array_unique($mentionedUserIds);

        if (empty($mentionedUserIds)) {
            return;
        }

        $users = User::whereIn('id', $mentionedUserIds)->get();

        foreach ($users as $user) {
            // Check if mention already exists to prevent duplicate notifications
            $existingMention = Mention::where([
                'user_id' => $user->id,
                'mentionable_type' => get_class($mentionable),
                'mentionable_id' => $mentionable->id,
            ])->exists();

            if ($existingMention) {
                continue;
            }

            // Create mention record
            Mention::create([
                'user_id' => $user->id,
                'mentioned_by_user_id' => $author->id,
                'mentionable_type' => get_class($mentionable),
                'mentionable_id' => $mentionable->id,
            ]);

            // Send notification
            $user->notify(new UserMentioned($mentionable, $author));
        }
    }

    /**
     * Extract user IDs from mention links in HTML.
     */
    protected function extractMentionedUserIds(string $html): array
    {
        $userIds = [];

        // Match data-mention-user-id attributes
        preg_match_all('/data-mention-user-id="(\d+)"/', $html, $matches);

        if (!empty($matches[1])) {
            $userIds = array_map('intval', $matches[1]);
        }

        return array_unique($userIds);
    }
}
