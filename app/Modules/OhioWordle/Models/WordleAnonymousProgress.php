<?php

namespace App\Modules\OhioWordle\Models;

use Database\Factories\WordleAnonymousProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordleAnonymousProgress extends Model
{
    use HasFactory;

    protected $table = 'wordle_anonymous_progress';

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

    protected static function newFactory(): WordleAnonymousProgressFactory
    {
        return WordleAnonymousProgressFactory::new();
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
