<?php

namespace Database\Factories;

use App\Models\VbCustomAvatar;
use App\Models\VbUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class VbCustomAvatarFactory extends Factory
{
    protected $model = VbCustomAvatar::class;

    public function definition(): array
    {
        return [
            'userid' => VbUser::factory(),
            'filename' => 'avatar'.fake()->unique()->numberBetween(1, 99999).'_1.gif',
        ];
    }
}
