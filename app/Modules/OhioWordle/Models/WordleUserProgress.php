<?php

namespace App\Modules\OhioWordle\Models;

use App\Models\User;
use Database\Factories\WordleUserProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordleUserProgress extends Model
{
    use HasFactory;

    protected $table = 'wordle_user_progress';

    protected $guarded = [];

    protected $casts = [
        'solved' => 'boolean',
        'guesses' => 'array',
        'feedback' => 'array',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'guesses' => '[]',
        'feedback' => '[]',
    ];

    protected static function newFactory(): WordleUserProgressFactory
    {
        return WordleUserProgressFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function word()
    {
        return $this->belongsTo(WordleWord::class, 'word_id');
    }

    /**
     * Add a guess and its feedback to the progress.
     */
    public function addGuess(string $guess, array $feedback): void
    {
        $guesses = $this->guesses ?? [];
        $guesses[] = strtoupper($guess);
        $this->guesses = $guesses;

        $feedbackHistory = $this->feedback ?? [];
        $feedbackHistory[] = $feedback;
        $this->feedback = $feedbackHistory;

        $this->attempts = count($guesses);
    }

    /**
     * Mark the game as complete.
     */
    public function complete(bool $won): void
    {
        $this->solved = $won;
        $this->completed_at = now();

        if ($won) {
            $this->guesses_taken = $this->attempts;
        }
    }
}
