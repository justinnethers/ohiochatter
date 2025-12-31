<?php

namespace App\Modules\Geography\DTOs;

use Carbon\Carbon;

readonly class UpdateContentData
{
    public function __construct(
        public ?int $contentTypeId = null,
        public ?array $categoryIds = null,
        public ?string $title = null,
        public ?string $body = null,
        public ?string $locatableType = null,
        public ?int $locatableId = null,
        public ?string $slug = null,
        public ?string $excerpt = null,
        public ?array $metadata = null,
        public ?string $featuredImage = null,
        public ?array $gallery = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?bool $featured = null,
        public ?Carbon $publishedAt = null,
        public bool $clearLocation = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contentTypeId: $data['content_type_id'] ?? null,
            categoryIds: $data['category_ids'] ?? null,
            title: $data['title'] ?? null,
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
            featured: $data['featured'] ?? null,
            publishedAt: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            clearLocation: $data['clear_location'] ?? false,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->contentTypeId !== null) {
            $data['content_type_id'] = $this->contentTypeId;
        }
        if ($this->title !== null) {
            $data['title'] = $this->title;
        }
        if ($this->body !== null) {
            $data['body'] = $this->body;
        }
        if ($this->clearLocation) {
            $data['locatable_type'] = null;
            $data['locatable_id'] = null;
        } elseif ($this->locatableType !== null) {
            $data['locatable_type'] = $this->locatableType;
            $data['locatable_id'] = $this->locatableId;
        }
        if ($this->slug !== null) {
            $data['slug'] = $this->slug;
        }
        if ($this->excerpt !== null) {
            $data['excerpt'] = $this->excerpt;
        }
        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }
        if ($this->featuredImage !== null) {
            $data['featured_image'] = $this->featuredImage;
        }
        if ($this->gallery !== null) {
            $data['gallery'] = $this->gallery;
        }
        if ($this->metaTitle !== null) {
            $data['meta_title'] = $this->metaTitle;
        }
        if ($this->metaDescription !== null) {
            $data['meta_description'] = $this->metaDescription;
        }
        if ($this->featured !== null) {
            $data['featured'] = $this->featured;
        }
        if ($this->publishedAt !== null) {
            $data['published_at'] = $this->publishedAt;
        }

        return $data;
    }
}
