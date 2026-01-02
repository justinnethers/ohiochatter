<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentRevisionFactory extends Factory
{
    protected $model = ContentRevision::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'blocks' => null,
            'metadata' => null,
            'featured_image' => null,
            'gallery' => null,
            'category_ids' => null,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory()->create(['is_admin' => true])->id,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by' => User::factory()->create(['is_admin' => true])->id,
            'reviewed_at' => now(),
            'review_notes' => fake()->sentence(),
        ]);
    }

    public function forContent(Content $content): static
    {
        return $this->state(fn (array $attributes) => [
            'content_id' => $content->id,
            'user_id' => $content->user_id,
        ]);
    }
}
