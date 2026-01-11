<?php

namespace Database\Factories;

use App\Modules\OhioWordle\Models\WordleWord;
use Illuminate\Database\Eloquent\Factories\Factory;

class WordleWordFactory extends Factory
{
    protected $model = WordleWord::class;

    public function definition(): array
    {
        $words = ['AKRON', 'DAYTON', 'TOLEDO', 'COLUMBUS', 'OHIO', 'GRANT', 'HAYES'];
        $word = fake()->randomElement($words);

        return [
            'word' => $word,
            'word_length' => strlen($word),
            'category' => fake()->randomElement(['city', 'landmark', 'person', 'misc']),
            'hint' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'publish_date' => fake()->unique()->date(),
            'is_active' => true,
        ];
    }

    public function today(): static
    {
        return $this->state(fn () => [
            'publish_date' => now()->toDateString(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function withWord(string $word): static
    {
        return $this->state(fn () => [
            'word' => strtoupper($word),
            'word_length' => strlen($word),
        ]);
    }
}
