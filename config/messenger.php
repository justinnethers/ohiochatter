<?php

return [

    'user_model' => App\Models\User::class,

    'message_model' => Cmgmyr\Messenger\Models\Message::class,

    'participant_model' => Cmgmyr\Messenger\Models\Participant::class,

    'thread_model' => Cmgmyr\Messenger\Models\Thread::class,

    /**
     * Define custom database table names - without prefixes.
     */
    'messages_table' => 'pm_messages',

    'participants_table' => 'pm_participants',

    'threads_table' => 'pm_threads',
];
