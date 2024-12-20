<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollVoteFactory extends Factory
{
    protected $model = PollVote::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'poll_option_id' => PollOption::factory(),
        ];
    }
}
