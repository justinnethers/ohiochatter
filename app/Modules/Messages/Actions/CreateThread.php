<?php

namespace App\Modules\Messages\Actions;

use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\DB;

class CreateThread
{
    public function execute(array $data, int $userId): Thread
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create thread
            $thread = Thread::create([
                'subject' => $data['subject'],
            ]);

            // Create message
            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'body' => $data['message'],
            ]);

            // Add sender as participant
            Participant::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'last_read' => new Carbon,
            ]);

            // Add other participants
            if (isset($data['recipients']) && !empty($data['recipients'])) {
                $thread->addParticipant($data['recipients']);
            }

            return $thread->fresh(['messages', 'participants']);
        });
    }
}
