<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class SeoData implements Arrayable
{
    public function __construct(
        public readonly string $title,
        public readonly string $description = '',
        public readonly ?string $keywords = null,
        public readonly ?string $canonical = null,
        public readonly string $robots = 'index, follow',
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly ?string $ogImage = null,
        public readonly string $ogType = 'website',
        public readonly ?string $ogUrl = null,
        public readonly string $twitterCard = 'summary_large_image',
        public readonly ?string $twitterTitle = null,
        public readonly ?string $twitterDescription = null,
        public readonly ?string $twitterImage = null,
        public readonly array $breadcrumbs = [],
        public readonly array $jsonLd = [],
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'canonical' => $this->canonical,
            'robots' => $this->robots,
            'og' => [
                'title' => $this->ogTitle ?? $this->title,
                'description' => $this->ogDescription ?? $this->description,
                'image' => $this->ogImage,
                'type' => $this->ogType,
                'url' => $this->ogUrl ?? $this->canonical,
            ],
            'twitter' => [
                'card' => $this->twitterCard,
                'title' => $this->twitterTitle ?? $this->ogTitle ?? $this->title,
                'description' => $this->twitterDescription ?? $this->ogDescription ?? $this->description,
                'image' => $this->twitterImage ?? $this->ogImage,
            ],
            'breadcrumbs' => $this->breadcrumbs,
            'jsonLd' => $this->jsonLd,
        ];
    }

    public function withTitle(string $title): self
    {
        return new self(
            title: $title,
            description: $this->description,
            keywords: $this->keywords,
            canonical: $this->canonical,
            robots: $this->robots,
            ogTitle: $this->ogTitle,
            ogDescription: $this->ogDescription,
            ogImage: $this->ogImage,
            ogType: $this->ogType,
            ogUrl: $this->ogUrl,
            twitterCard: $this->twitterCard,
            twitterTitle: $this->twitterTitle,
            twitterDescription: $this->twitterDescription,
            twitterImage: $this->twitterImage,
            breadcrumbs: $this->breadcrumbs,
            jsonLd: $this->jsonLd,
        );
    }

    public function withBreadcrumbs(array $breadcrumbs): self
    {
        return new self(
            title: $this->title,
            description: $this->description,
            keywords: $this->keywords,
            canonical: $this->canonical,
            robots: $this->robots,
            ogTitle: $this->ogTitle,
            ogDescription: $this->ogDescription,
            ogImage: $this->ogImage,
            ogType: $this->ogType,
            ogUrl: $this->ogUrl,
            twitterCard: $this->twitterCard,
            twitterTitle: $this->twitterTitle,
            twitterDescription: $this->twitterDescription,
            twitterImage: $this->twitterImage,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->jsonLd,
        );
    }

    public function withJsonLd(array $jsonLd): self
    {
        return new self(
            title: $this->title,
            description: $this->description,
            keywords: $this->keywords,
            canonical: $this->canonical,
            robots: $this->robots,
            ogTitle: $this->ogTitle,
            ogDescription: $this->ogDescription,
            ogImage: $this->ogImage,
            ogType: $this->ogType,
            ogUrl: $this->ogUrl,
            twitterCard: $this->twitterCard,
            twitterTitle: $this->twitterTitle,
            twitterDescription: $this->twitterDescription,
            twitterImage: $this->twitterImage,
            breadcrumbs: $this->breadcrumbs,
            jsonLd: $jsonLd,
        );
    }
}