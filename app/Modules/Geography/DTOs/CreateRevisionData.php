<?php

namespace App\Modules\Geography\DTOs;

readonly class CreateRevisionData
{
    public function __construct(
        public int $contentId,
        public string $title,
        public ?string $excerpt = null,
        public ?string $body = null,
        public ?array $blocks = null,
        public ?array $metadata = null,
        public ?string $featuredImage = null,
        public ?array $gallery = null,
        public ?array $categoryIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contentId: $data['content_id'],
            title: $data['title'],
            excerpt: $data['excerpt'] ?? null,
            body: $data['body'] ?? null,
            blocks: $data['blocks'] ?? null,
            metadata: $data['metadata'] ?? null,
            featuredImage: $data['featured_image'] ?? null,
            gallery: $data['gallery'] ?? null,
            categoryIds: $data['category_ids'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'content_id' => $this->contentId,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'blocks' => $this->blocks,
            'metadata' => $this->metadata,
            'featured_image' => $this->featuredImage,
            'gallery' => $this->gallery,
            'category_ids' => $this->categoryIds,
        ];
    }
}
