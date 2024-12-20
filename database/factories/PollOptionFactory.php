<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollOptionFactory extends Factory
{
    protected $model = PollOption::class;

    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'label' => fake()->words(3, true),
        ];
    }

    public function withVotes(?int $count = null): static
    {
        return $this->afterCreating(function (PollOption $option) use ($count) {
            $voteCount = $count ?? fake()->numberBetween(1, 10);
            PollVote::factory()->count($voteCount)->create([
                'poll_option_id' => $option->id,
            ]);
        });
    }
}
