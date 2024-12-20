<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Forum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ForumFactory extends Factory
{
    protected $model = Forum::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'creator_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'color' => fake()->hexColor(),
            'is_active' => true,
            'is_restricted' => false,
        ];
    }

    public function restricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_restricted' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
