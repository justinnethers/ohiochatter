<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\Rep;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepFactory extends Factory
{
    protected $model = Rep::class;

    public function definition(): array
    {
        $repped = fake()->randomElement([
            Thread::factory()->create(),
            Reply::factory()->create(),
        ]);

        return [
            'user_id' => User::factory(),
            'repped_id' => $repped->id,
            'repped_type' => get_class($repped),
        ];
    }

    public function forThread(?Thread $thread = null): static
    {
        return $this->state(function (array $attributes) use ($thread) {
            $thread = $thread ?? Thread::factory()->create();
            return [
                'repped_id' => $thread->id,
                'repped_type' => Thread::class,
            ];
        });
    }

    public function forReply(?Reply $reply = null): static
    {
        return $this->state(function (array $attributes) use ($reply) {
            $reply = $reply ?? Reply::factory()->create();
            return [
                'repped_id' => $reply->id,
                'repped_type' => Reply::class,
            ];
        });
    }
}
