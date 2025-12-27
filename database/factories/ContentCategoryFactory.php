<?php

namespace Database\Factories;

use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentCategoryFactory extends Factory
{
    protected $model = ContentCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Restaurants',
            'Attractions',
            'Nightlife',
            'Shopping',
            'Outdoors',
            'Sports',
            'Events',
            'History',
            'Arts & Culture',
            'Family Fun',
        ]);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'meta_title' => '',
            'meta_description' => '',
            'display_order' => fake()->numberBetween(1, 100),
            'icon' => fake()->randomElement(['utensils', 'landmark', 'glass-cheers', 'shopping-bag', 'tree', 'football-ball']),
        ];
    }

    public function withParent(ContentCategory $parent): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
