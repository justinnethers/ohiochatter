<?php

namespace App\Notifications;

use App\Models\ContentRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionRejected extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Your Guide Revision Was Not Approved')
            ->greeting('Revision Not Approved')
            ->line('Unfortunately, your proposed changes to the guide were not approved:')
            ->line("**{$this->revision->content->title}**");

        if ($this->revision->review_notes) {
            $message->line("Reason: {$this->revision->review_notes}");
        }

        return $message
            ->action('Edit Guide', route('guide.edit-content', $this->revision->content))
            ->line('You can make adjustments and submit a new revision.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'revision_id' => $this->revision->id,
            'content_id' => $this->revision->content_id,
            'content_title' => $this->revision->content->title,
            'review_notes' => $this->revision->review_notes,
            'type' => 'revision_rejected',
        ];
    }
}
