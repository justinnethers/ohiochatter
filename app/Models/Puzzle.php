<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Puzzle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'publish_date' => 'date',
        'alternate_answers' => 'array',
    ];

    /**
     * Get the puzzle for today
     *
     * @return Puzzle|null
     */
    public static function getTodaysPuzzle()
    {
        return self::where('publish_date', Carbon::today()->toDateString())->first();
    }

    /**
     * Get user progress for this puzzle
     */
    public function userProgress()
    {
        return $this->hasMany(UserGameProgress::class);
    }

    /**
     * Check if a guess matches any valid answer (primary or alternate)
     *
     * @param string $guess
     * @return bool
     */
    public function isCorrectAnswer(string $guess): bool
    {
        $normalizedGuess = strtolower(trim($guess));
        $normalizedAnswer = strtolower(trim($this->answer));

        if ($normalizedGuess === $normalizedAnswer) {
            return true;
        }

        if (!empty($this->alternate_answers)) {
            foreach ($this->alternate_answers as $alternate) {
                if (strtolower(trim($alternate)) === $normalizedGuess) {
                    return true;
                }
            }
        }

        return false;
    }
}
