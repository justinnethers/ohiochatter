<?php

namespace App\Models;

use App\Actions\BuckEye\OpenAIAnswerCheck;
use App\Actions\BuckEye\RobustAnswerCheck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

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
     * Check if a guess is correct, considering exact matches and AI similarity check
     *
     * @param string $guess
     * @return bool
     */
    public function isCorrectAnswer(string $guess): bool
    {
        if ($this->exactAnswerMatch($guess)) {
            return true;
        }

        try {
            $allAnswers = array_merge([$this->answer], $this->alternate_answers ?? []);

            $robustAnswerCheck = App::make(RobustAnswerCheck::class);
            return $robustAnswerCheck($allAnswers, $guess);

        } catch (\Exception $e) {
            // Fall back to exact matching on error
            return false;
        }
    }

    /**
     * Check if a guess exactly matches any valid answer (primary or alternate)
     *
     * @param string $guess
     * @return bool
     */
    public function exactAnswerMatch(string $guess): bool
    {
        // Normalize the guess and main answer for comparison
        $normalizedGuess = strtolower(trim($guess));
        $normalizedAnswer = strtolower(trim($this->answer));

        // Check if the guess matches the main answer
        if ($normalizedGuess === $normalizedAnswer) {
            return true;
        }

        // Check if the guess matches any alternate answer
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
