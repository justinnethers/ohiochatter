<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Pickem\Models\PickemMatchup;
use App\Modules\Pickem\Models\PickemPick;
use Illuminate\Database\Eloquent\Factories\Factory;

class PickemPickFactory extends Factory
{
    protected $model = PickemPick::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pickem_matchup_id' => PickemMatchup::factory(),
            'pick' => fake()->randomElement(['a', 'b']),
            'confidence' => null,
        ];
    }

    public function pickA(): static
    {
        return $this->state(fn () => ['pick' => 'a']);
    }

    public function pickB(): static
    {
        return $this->state(fn () => ['pick' => 'b']);
    }

    public function withConfidence(int $confidence): static
    {
        return $this->state(fn () => ['confidence' => $confidence]);
    }
}
