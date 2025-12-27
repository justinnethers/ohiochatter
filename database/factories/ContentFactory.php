<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentType;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'content_type_id' => ContentType::factory(),
            'content_category_id' => ContentCategory::factory(),
            'user_id' => User::factory(),
            'locatable_type' => null,
            'locatable_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'body' => fake()->paragraphs(5, true),
            'metadata' => null,
            'featured_image' => null,
            'gallery' => null,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'featured' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'featured' => true,
        ]);
    }

    public function forRegion(Region $region): static
    {
        return $this->state(fn(array $attributes) => [
            'locatable_type' => Region::class,
            'locatable_id' => $region->id,
        ]);
    }

    public function forCounty(\App\Models\County $county): static
    {
        return $this->state(fn(array $attributes) => [
            'locatable_type' => \App\Models\County::class,
            'locatable_id' => $county->id,
        ]);
    }

    public function forCity(\App\Models\City $city): static
    {
        return $this->state(fn(array $attributes) => [
            'locatable_type' => \App\Models\City::class,
            'locatable_id' => $city->id,
        ]);
    }

    public function forLocation(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'locatable_type' => $type,
            'locatable_id' => $id,
        ]);
    }

    public function inCategory(ContentCategory $category): static
    {
        return $this->state(fn(array $attributes) => [
            'content_category_id' => $category->id,
        ]);
    }

    public function ofType(ContentType $type): static
    {
        return $this->state(fn(array $attributes) => [
            'content_type_id' => $type->id,
        ]);
    }

    public function byAuthor(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
