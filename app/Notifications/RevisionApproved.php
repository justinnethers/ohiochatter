<?php

namespace App\Notifications;

use App\Models\ContentRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionApproved extends Notification implements ShouldQueue
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
            ->subject('Your Guide Revision Was Approved')
            ->greeting('Revision Approved!')
            ->line('Your proposed changes to the guide have been approved and applied:')
            ->line("**{$this->revision->content->title}**")
            ->action('View Guide', route('guide.show', $this->revision->content))
            ->line('Thank you for contributing to the community!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'revision_id' => $this->revision->id,
            'content_id' => $this->revision->content_id,
            'content_title' => $this->revision->content->title,
            'type' => 'revision_approved',
        ];
    }
}
