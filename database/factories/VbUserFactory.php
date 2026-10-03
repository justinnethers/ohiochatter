<?php

namespace Database\Factories;

use App\Models\VbUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class VbUserFactory extends Factory
{
    protected $model = VbUser::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'usertitle' => fake()->jobTitle(),
            'posts' => fake()->numberBetween(0, 5000),
        ];
    }
}
