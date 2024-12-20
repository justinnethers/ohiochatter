<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Search;
use Illuminate\Database\Eloquent\Factories\Factory;

class SearchFactory extends Factory
{
    protected $model = Search::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'search_terms' => fake()->words(3, true),
            'search_filters' => json_encode([
                'forum' => fake()->numberBetween(1, 5),
                'date' => 'last_month'
            ]),
        ];
    }

    public function withoutFilters(): static
    {
        return $this->state(fn (array $attributes) => [
            'search_filters' => null,
        ]);
    }
}
