<?php

namespace App\Modules\OhioWordle\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WordioRejectedGuess extends Model
{
    protected $table = 'wordio_rejected_guesses';

    protected $guarded = [];

    public const REASON_NOT_IN_DICTIONARY = 'not_in_dictionary';
    public const REASON_WRONG_LENGTH = 'wrong_length';
    public const REASON_EMPTY = 'empty';

    public function word()
    {
        return $this->belongsTo(WordleWord::class, 'word_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $guess,
        string $reason,
        ?int $wordId = null,
        ?int $userId = null,
        ?string $sessionId = null
    ): self {
        return self::create([
            'word_id' => $wordId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'guess' => strtoupper(substr(trim($guess), 0, 20)),
            'reason' => $reason,
            'ip_address' => request()->ip(),
        ]);
    }
}