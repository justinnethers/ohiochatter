<?php

namespace Database\Factories;

use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\GuideDraft;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuideDraftFactory extends Factory
{
    protected $model = GuideDraft::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'excerpt' => fake()->paragraph(2),
            'body' => fake()->paragraphs(5, true),
            'content_category_id' => null,
            'content_type_id' => null,
            'locatable_type' => null,
            'locatable_id' => null,
            'featured_image' => null,
            'gallery' => null,
        ];
    }

    public function empty(): static
    {
        return $this->state(fn(array $attributes) => [
            'title' => null,
            'excerpt' => null,
            'body' => null,
        ]);
    }

    public function complete(): static
    {
        return $this->state(fn(array $attributes) => [
            'content_category_id' => ContentCategory::factory(),
            'content_type_id' => ContentType::factory(),
            'locatable_type' => Region::class,
            'locatable_id' => Region::factory(),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forRegion(Region $region): static
    {
        return $this->state(fn(array $attributes) => [
            'locatable_type' => Region::class,
            'locatable_id' => $region->id,
        ]);
    }
}
