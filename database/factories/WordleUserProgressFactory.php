<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\OhioWordle\Models\WordleUserProgress;
use App\Modules\OhioWordle\Models\WordleWord;
use Illuminate\Database\Eloquent\Factories\Factory;

class WordleUserProgressFactory extends Factory
{
    protected $model = WordleUserProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'word_id' => WordleWord::factory(),
            'solved' => false,
            'attempts' => 0,
            'guesses_taken' => null,
            'guesses' => [],
            'feedback' => [],
            'completed_at' => null,
        ];
    }

    public function solved(int $guesses = 1): static
    {
        $guessArray = array_fill(0, $guesses, 'GUESS');

        return $this->state(fn () => [
            'solved' => true,
            'attempts' => $guesses,
            'guesses_taken' => $guesses,
            'guesses' => $guessArray,
            'feedback' => array_fill(0, $guesses, ['correct', 'correct', 'correct', 'correct', 'correct']),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'solved' => false,
            'attempts' => 6,
            'guesses_taken' => null,
            'guesses' => array_fill(0, 6, 'WRONG'),
            'feedback' => array_fill(0, 6, ['absent', 'absent', 'absent', 'absent', 'absent']),
            'completed_at' => now(),
        ]);
    }
}
