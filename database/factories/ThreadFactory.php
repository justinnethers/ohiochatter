<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\Poll;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        $title = fake()->sentence();
        return [
            'user_id' => User::factory(),
            'forum_id' => Forum::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => fake()->paragraphs(3, true),
            'views' => fake()->numberBetween(0, 1000),
            'locked' => false,
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'locked' => true,
        ]);
    }

    public function withReplies(int $count = 3): static
    {
        return $this->has(Reply::factory()->count($count), 'replies');
    }

    public function withPoll(): static
    {
        return $this->has(Poll::factory(), 'poll');
    }
}
