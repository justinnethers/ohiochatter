<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\OhioWordle\Models\WordleUserStats;
use Illuminate\Database\Eloquent\Factories\Factory;

class WordleUserStatsFactory extends Factory
{
    protected $model = WordleUserStats::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'games_played' => 0,
            'games_won' => 0,
            'current_streak' => 0,
            'max_streak' => 0,
            'guess_distribution' => [],
            'last_played_date' => null,
        ];
    }

    public function withStreak(int $streak): static
    {
        return $this->state(fn () => [
            'current_streak' => $streak,
            'max_streak' => $streak,
            'games_played' => $streak,
            'games_won' => $streak,
        ]);
    }

    public function withGames(int $played, int $won): static
    {
        return $this->state(fn () => [
            'games_played' => $played,
            'games_won' => $won,
        ]);
    }
}
