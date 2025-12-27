<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'county_id' => County::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'meta_title' => $name . ', Ohio | OhioChatter',
            'meta_description' => fake()->sentence(),
            'is_major' => fake()->boolean(30),
            'coordinates' => [
                'lat' => fake()->latitude(38.4, 41.9),
                'lng' => fake()->longitude(-84.8, -80.5),
            ],
            'population' => fake()->numberBetween(1000, 100000),
            'demographics' => [
                'median_income' => fake()->numberBetween(35000, 120000),
                'median_age' => fake()->numberBetween(28, 50),
            ],
            'incorporated_year' => fake()->numberBetween(1901, 2020),
        ];
    }

    public function forCounty(County $county): static
    {
        return $this->state(fn(array $attributes) => [
            'county_id' => $county->id,
        ]);
    }

    public function major(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_major' => true,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
