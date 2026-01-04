<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PickemCommentFactory extends Factory
{
    protected $model = PickemComment::class;

    public function definition(): array
    {
        return [
            'pickem_id' => Pickem::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
