<?php

namespace Database\Factories;

use App\Models\VbPost;
use App\Models\VbThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VbPost>
 */
class VbPostFactory extends Factory
{
    protected $model = VbPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'threadid' => VbThread::factory(),
            'userid' => fake()->numberBetween(1, 1000),
            'username' => fake()->userName(),
            'dateline' => now()->subDays(rand(1, 365))->timestamp,
            'pagetext' => fake()->paragraph(),
            'visible' => 1,
        ];
    }
}
