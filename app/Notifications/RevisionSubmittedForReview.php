<?php

namespace App\Notifications;

use App\Models\ContentRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionSubmittedForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContentRevision $revision
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Content Revision Submitted for Review')
            ->greeting('Revision Submitted')
            ->line("{$this->revision->author->username} has submitted changes to:")
            ->line("**{$this->revision->content->title}**")
            ->line("Proposed new title: **{$this->revision->title}**")
            ->action('Review Revision', url('/admin/content-revisions/' . $this->revision->id))
            ->line('Please review and approve or reject this revision.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'revision_id' => $this->revision->id,
            'content_id' => $this->revision->content_id,
            'content_title' => $this->revision->content->title,
            'proposed_title' => $this->revision->title,
            'author_id' => $this->revision->user_id,
            'author_username' => $this->revision->author->username,
            'type' => 'revision_submitted',
        ];
    }
}
