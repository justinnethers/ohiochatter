<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\BuckEYE\Models\UserGameStats;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGameStatsFactory extends Factory
{
    protected $model = UserGameStats::class;

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

    public function withGames(int $played, int $won): static
    {
        return $this->state(fn () => [
            'games_played' => $played,
            'games_won' => $won,
        ]);
    }

    public function withStreak(int $current, int $max = null): static
    {
        return $this->state(fn () => [
            'current_streak' => $current,
            'max_streak' => $max ?? $current,
        ]);
    }

    public function withDistribution(array $distribution): static
    {
        return $this->state(fn () => [
            'guess_distribution' => $distribution,
        ]);
    }

    public function playedToday(): static
    {
        return $this->state(fn () => [
            'last_played_date' => now()->toDateString(),
        ]);
    }
}
