<?php

namespace Database\Factories;

use App\Models\VbForum;
use Illuminate\Database\Eloquent\Factories\Factory;

class VbForumFactory extends Factory
{
    protected $model = VbForum::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'title_clean' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'threadcount' => fake()->numberBetween(10, 1000),
            'replycount' => fake()->numberBetween(100, 10000),
            'parentid' => 1,
            'displayorder' => fake()->numberBetween(1, 10),
            'lastposter' => fake()->userName(),
            'lastpost' => now()->timestamp,
        ];
    }
}