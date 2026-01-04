<?php

namespace Database\Factories;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemMatchup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PickemMatchupFactory extends Factory
{
    protected $model = PickemMatchup::class;

    private static array $teams = [
        ['Bengals', 'Browns'],
        ['Steelers', 'Ravens'],
        ['Chiefs', 'Raiders'],
        ['Cowboys', 'Eagles'],
        ['Packers', 'Bears'],
        ['49ers', 'Seahawks'],
        ['Bills', 'Dolphins'],
        ['Patriots', 'Jets'],
    ];

    public function definition(): array
    {
        $matchup = fake()->randomElement(self::$teams);

        return [
            'pickem_id' => Pickem::factory(),
            'option_a' => $matchup[0],
            'option_b' => $matchup[1],
            'description' => fake()->optional()->sentence(3),
            'points' => 1,
            'display_order' => 0,
            'winner' => null,
        ];
    }

    public function withWinner(string $winner = 'a'): static
    {
        return $this->state(fn () => ['winner' => $winner]);
    }

    public function push(): static
    {
        return $this->state(fn () => ['winner' => 'push']);
    }

    public function withPoints(int $points): static
    {
        return $this->state(fn () => ['points' => $points]);
    }
}
