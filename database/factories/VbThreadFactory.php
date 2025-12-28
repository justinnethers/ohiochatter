<?php

namespace Database\Factories;

use App\Models\VbForum;
use App\Models\VbThread;
use Illuminate\Database\Eloquent\Factories\Factory;

class VbThreadFactory extends Factory
{
    protected $model = VbThread::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'forumid' => VbForum::factory(),
            'postuserid' => fake()->numberBetween(1, 1000),
            'postusername' => fake()->userName(),
            'dateline' => now()->subDays(rand(1, 365))->timestamp,
            'lastpost' => now()->timestamp,
            'lastposter' => fake()->userName(),
            'replycount' => fake()->numberBetween(0, 100),
            'views' => fake()->numberBetween(10, 5000),
            'visible' => 1,
            'open' => 1,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'visible' => 0,
        ]);
    }
}