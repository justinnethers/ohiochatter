<?php

namespace App\Modules\Geography\DTOs;

use Carbon\Carbon;

readonly class CreateContentData
{
    public function __construct(
        public ?int $contentTypeId,
        public array $categoryIds,
        public string $title,
        public ?string $body,
        public ?string $locatableType = null,
        public ?int $locatableId = null,
        public ?string $slug = null,
        public ?string $excerpt = null,
        public ?array $metadata = null,
        public ?string $featuredImage = null,
        public ?array $gallery = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public bool $featured = false,
        public ?Carbon $publishedAt = null,
        public ?array $blocks = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contentTypeId: $data['content_type_id'] ?? null,
            categoryIds: $data['category_ids'] ?? [],
            title: $data['title'],
            body: $data['body'] ?? null,
            locatableType: $data['locatable_type'] ?? null,
            locatableId: $data['locatable_id'] ?? null,
            slug: $data['slug'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            metadata: $data['metadata'] ?? null,
            featuredImage: $data['featured_image'] ?? null,
            gallery: $data['gallery'] ?? null,
            metaTitle: $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            featured: $data['featured'] ?? false,
            publishedAt: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            blocks: $data['blocks'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'content_type_id' => $this->contentTypeId,
            'title' => $this->title,
            'body' => $this->body,
            'locatable_type' => $this->locatableType,
            'locatable_id' => $this->locatableId,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'metadata' => $this->metadata,
            'featured_image' => $this->featuredImage,
            'gallery' => $this->gallery,
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'featured' => $this->featured,
            'published_at' => $this->publishedAt,
            'blocks' => $this->blocks,
        ];
    }
}
