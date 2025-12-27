<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true) . ' Ohio';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'meta_title' => $name . ' | OhioChatter',
            'meta_description' => fake()->sentence(),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
