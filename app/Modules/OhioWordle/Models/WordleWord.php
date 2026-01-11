<?php

namespace App\Modules\OhioWordle\Models;

use Database\Factories\WordleWordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WordleWord extends Model
{
    use HasFactory;

    protected $table = 'wordle_words';

    protected $guarded = [];

    protected $casts = [
        'publish_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (WordleWord $word) {
            $word->word_length = strlen($word->word);
            $word->word = strtoupper($word->word);
        });
    }

    /**
     * Get the word for today.
     */
    public static function getTodaysWord(): ?self
    {
        return self::where('publish_date', Carbon::today()->toDateString())
            ->where('is_active', true)
            ->first();
    }

    protected static function newFactory(): WordleWordFactory
    {
        return WordleWordFactory::new();
    }

    /**
     * User progress for this word.
     */
    public function userProgress()
    {
        return $this->hasMany(WordleUserProgress::class, 'word_id');
    }

    /**
     * Anonymous progress for this word.
     */
    public function anonymousProgress()
    {
        return $this->hasMany(WordleAnonymousProgress::class, 'word_id');
    }

    /**
     * Check if a guess matches this word.
     */
    public function isCorrectGuess(string $guess): bool
    {
        return strtoupper(trim($guess)) === strtoupper($this->word);
    }
}
