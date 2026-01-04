<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemGroup;
use App\Modules\Pickem\Models\PickemMatchup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PickemFactory extends Factory
{
    protected $model = Pickem::class;

    public function definition(): array
    {
        $title = fake()->words(4, true);

        return [
            'user_id' => User::factory(),
            'pickem_group_id' => null,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->randomNumber(5),
            'body' => fake()->optional()->paragraph(),
            'scoring_type' => 'simple',
            'picks_lock_at' => null,
            'is_finalized' => false,
        ];
    }

    public function inGroup(PickemGroup $group = null): static
    {
        return $this->state(fn() => [
            'pickem_group_id' => $group?->id ?? PickemGroup::factory(),
        ]);
    }

    public function withMatchups(int $count = 3): static
    {
        return $this->has(PickemMatchup::factory()->count($count), 'matchups');
    }

    public function locked(): static
    {
        return $this->state(fn() => [
            'picks_lock_at' => now()->subHour(),
        ]);
    }

    public function unlocked(): static
    {
        return $this->state(fn() => [
            'picks_lock_at' => now()->addDay(),
        ]);
    }

    public function simple(): static
    {
        return $this->state(fn() => ['scoring_type' => 'simple']);
    }

    public function weighted(): static
    {
        return $this->state(fn() => ['scoring_type' => 'weighted']);
    }

    public function confidence(): static
    {
        return $this->state(fn() => ['scoring_type' => 'confidence']);
    }

    public function finalized(): static
    {
        return $this->state(fn() => ['is_finalized' => true]);
    }
}
