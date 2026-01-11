<?php

namespace Database\Factories;

use App\Modules\BuckEYE\Models\Puzzle;
use Illuminate\Database\Eloquent\Factories\Factory;

class PuzzleFactory extends Factory
{
    protected $model = Puzzle::class;

    public function definition(): array
    {
        $answer = fake()->words(fake()->numberBetween(1, 3), true);

        return [
            'publish_date' => fake()->unique()->date(),
            'answer' => $answer,
            'word_count' => str_word_count($answer),
            'image_path' => 'puzzles/test-image.jpg',
            'category' => fake()->randomElement(['person', 'place', 'thing', 'landmark', 'business']),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'hint' => fake()->sentence(),
            'alternate_answers' => [],
            'image_attribution' => fake()->optional()->sentence(),
        ];
    }

    public function today(): static
    {
        return $this->state(fn () => [
            'publish_date' => now()->toDateString(),
        ]);
    }

    public function withAlternateAnswers(array $answers): static
    {
        return $this->state(fn () => [
            'alternate_answers' => $answers,
        ]);
    }

    public function easy(): static
    {
        return $this->state(fn () => [
            'difficulty' => 'easy',
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn () => [
            'difficulty' => 'medium',
        ]);
    }

    public function hard(): static
    {
        return $this->state(fn () => [
            'difficulty' => 'hard',
        ]);
    }
}
