<?php

namespace App\Notifications;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $mentionable,
        public User $mentionedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mention',
            'mentioned_by_id' => $this->mentionedBy->id,
            'mentioned_by_username' => $this->mentionedBy->username,
            'mentioned_by_avatar' => $this->mentionedBy->avatar_path,
            'mentionable_type' => $this->mentionable instanceof Thread ? 'thread' : 'reply',
            'mentionable_id' => $this->mentionable->id,
            'thread_title' => $this->getThreadTitle(),
            'url' => $this->getMentionUrl(),
        ];
    }

    protected function getThreadTitle(): string
    {
        if ($this->mentionable instanceof Thread) {
            return $this->mentionable->title;
        }

        return $this->mentionable->thread->title;
    }

    protected function getMentionUrl(): string
    {
        if ($this->mentionable instanceof Thread) {
            return $this->mentionable->path();
        }

        // For replies, calculate the page number
        $reply = $this->mentionable;
        $thread = $reply->thread;
        $position = $thread->replies()->where('id', '<=', $reply->id)->count();
        $perPage = 20;
        $page = ceil($position / $perPage);

        return $thread->path("?page={$page}#reply-{$reply->id}");
    }
}
