<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\Neg;
use Illuminate\Database\Eloquent\Factories\Factory;

class NegFactory extends Factory
{
    protected $model = Neg::class;

    public function definition(): array
    {
        $negged = fake()->randomElement([
            Thread::factory()->create(),
            Reply::factory()->create(),
        ]);

        return [
            'user_id' => User::factory(),
            'negged_id' => $negged->id,
            'negged_type' => get_class($negged),
        ];
    }

    public function forThread(?Thread $thread = null): static
    {
        return $this->state(function (array $attributes) use ($thread) {
            $thread = $thread ?? Thread::factory()->create();
            return [
                'negged_id' => $thread->id,
                'negged_type' => Thread::class,
            ];
        });
    }

    public function forReply(?Reply $reply = null): static
    {
        return $this->state(function (array $attributes) use ($reply) {
            $reply = $reply ?? Reply::factory()->create();
            return [
                'negged_id' => $reply->id,
                'negged_type' => Reply::class,
            ];
        });
    }
}
