<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Thread;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollFactory extends Factory
{
    protected $model = Poll::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'thread_id' => Thread::factory(),
            'type' => fake()->randomElement(['single', 'multiple']),
        ];
    }

    public function withOptions(int $count = 4): static
    {
        return $this->has(PollOption::factory()->count($count), 'pollOptions');
    }
}
