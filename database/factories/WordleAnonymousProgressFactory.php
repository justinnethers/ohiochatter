<?php

namespace Database\Factories;

use App\Modules\OhioWordle\Models\WordleAnonymousProgress;
use App\Modules\OhioWordle\Models\WordleWord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WordleAnonymousProgressFactory extends Factory
{
    protected $model = WordleAnonymousProgress::class;

    public function definition(): array
    {
        return [
            'word_id' => WordleWord::factory(),
            'session_id' => Str::random(40),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
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
