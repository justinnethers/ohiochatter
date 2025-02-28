<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Thread;
use App\Models\Reply;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReplyFactory extends Factory
{
    protected $model = Reply::class;

    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraphs(2, true),
        ];
    }
}
