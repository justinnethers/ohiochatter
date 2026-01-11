<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGameProgressFactory extends Factory
{
    protected $model = UserGameProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'puzzle_id' => Puzzle::factory(),
            'solved' => false,
            'attempts' => 0,
            'guesses_taken' => null,
            'previous_guesses' => [],
            'completed_at' => null,
        ];
    }

    public function solved(int $guesses = 3): static
    {
        $previousGuesses = [];
        for ($i = 0; $i < $guesses - 1; $i++) {
            $previousGuesses[] = fake()->words(2, true);
        }
        $previousGuesses[] = 'correct answer';

        return $this->state(fn () => [
            'solved' => true,
            'attempts' => $guesses,
            'guesses_taken' => $guesses,
            'previous_guesses' => $previousGuesses,
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        $previousGuesses = [];
        for ($i = 0; $i < 5; $i++) {
            $previousGuesses[] = fake()->words(2, true);
        }

        return $this->state(fn () => [
            'solved' => false,
            'attempts' => 5,
            'guesses_taken' => null,
            'previous_guesses' => $previousGuesses,
            'completed_at' => now(),
        ]);
    }

    public function inProgress(int $attempts = 2): static
    {
        $previousGuesses = [];
        for ($i = 0; $i < $attempts; $i++) {
            $previousGuesses[] = fake()->words(2, true);
        }

        return $this->state(fn () => [
            'solved' => false,
            'attempts' => $attempts,
            'guesses_taken' => null,
            'previous_guesses' => $previousGuesses,
            'completed_at' => null,
        ]);
    }
}
