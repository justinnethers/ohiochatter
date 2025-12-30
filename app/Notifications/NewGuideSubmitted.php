<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewGuideSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Content $content
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locationName = $this->content->locatable?->name ?? 'Ohio';
        $categoryName = $this->content->contentCategory?->name ?? 'Uncategorized';

        return (new MailMessage)
            ->subject('New Guide Submitted for Review')
            ->greeting('New Guide Submission')
            ->line("A new guide has been submitted by {$this->content->author->username}:")
            ->line("**{$this->content->title}**")
            ->line("Location: {$locationName}")
            ->line("Category: {$categoryName}")
            ->action('Review in Admin', url('/admin/contents/' . $this->content->id))
            ->line('Please review and approve or reject this submission.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'author_id' => $this->content->user_id,
            'author_username' => $this->content->author->username,
            'type' => 'new_guide_submission',
        ];
    }
}
