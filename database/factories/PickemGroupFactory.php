<?php

namespace Database\Factories;

use App\Modules\Pickem\Models\PickemGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PickemGroupFactory extends Factory
{
    protected $model = PickemGroup::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
