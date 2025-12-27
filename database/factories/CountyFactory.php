<?php

namespace Database\Factories;

use App\Models\County;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CountyFactory extends Factory
{
    protected $model = County::class;

    public function definition(): array
    {
        $name = fake()->unique()->lastName() . ' County';

        return [
            'region_id' => Region::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'meta_title' => $name . ', Ohio | OhioChatter',
            'meta_description' => fake()->sentence(),
            'demographics' => [
                'population' => fake()->numberBetween(10000, 500000),
                'median_age' => fake()->numberBetween(30, 45),
            ],
            'county_seat' => fake()->city(),
            'founded_year' => fake()->numberBetween(1800, 1900),
        ];
    }

    public function forRegion(Region $region): static
    {
        return $this->state(fn(array $attributes) => [
            'region_id' => $region->id,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
