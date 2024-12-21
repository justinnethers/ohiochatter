<?php

namespace App\Modules\Messages\Actions;

use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\DB;

class AddMessage
{
    public function execute(Thread $thread, array $data, int $userId): Message
    {
        return DB::transaction(function () use ($thread, $data, $userId) {
            // Create message
            $message = Message::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'body' => $data['body'],
            ]);

            // Add participant if not exists
            $participant = Participant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $userId,
            ]);

            // Update last_read timestamp
            $participant->update(['last_read' => new Carbon]);

            // Add any new recipients if specified
            if (isset($data['recipients']) && !empty($data['recipients'])) {
                $thread->addParticipant($data['recipients']);
            }

            return $message->fresh('user');
        });
    }
}
