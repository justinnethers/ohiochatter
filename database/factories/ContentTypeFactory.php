<?php

namespace Database\Factories;

use App\Models\ContentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentTypeFactory extends Factory
{
    protected $model = ContentType::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Article', 'Guide', 'List', 'Review', 'Directory']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'required_fields' => ['title', 'body'],
            'optional_fields' => ['featured_image', 'gallery'],
        ];
    }

    public function article(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Article',
            'slug' => 'article',
            'required_fields' => ['title', 'body', 'excerpt'],
            'optional_fields' => ['featured_image', 'gallery', 'author_bio'],
        ]);
    }

    public function guide(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Guide',
            'slug' => 'guide',
            'required_fields' => ['title', 'introduction', 'sections'],
            'optional_fields' => ['tips', 'warnings', 'related_links'],
        ]);
    }

    public function list(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'List',
            'slug' => 'list',
            'required_fields' => ['title', 'introduction', 'items'],
            'optional_fields' => ['criteria', 'methodology', 'featured_image'],
        ]);
    }
}
