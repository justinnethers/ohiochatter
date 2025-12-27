<?php

namespace App\Services;

use App\Models\Forum;
use App\Models\Thread;
use App\ValueObjects\SeoData;
use Illuminate\Support\Str;

class SeoService
{
    protected string $siteName = 'OhioChatter';
    protected string $defaultImage = '/images/og-default.jpg';
    protected string $siteUrl;

    public function __construct()
    {
        $this->siteUrl = config('app.url');
    }

    /**
     * Generate SEO data for the homepage
     */
    public function forHomepage(): SeoData
    {
        $description = 'The Ohio community forum for discussions, local guides, and connecting with fellow Ohioans. Join conversations about Ohio sports, politics, and local happenings.';

        return new SeoData(
            title: 'OhioChatter - Ohio Community Forum & Local Guides',
            description: $description,
            canonical: $this->siteUrl,
            ogTitle: 'OhioChatter - Ohio Community Forum',
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'website',
            ogUrl: $this->siteUrl,
            breadcrumbs: $this->buildBreadcrumbs([]),
            jsonLd: $this->buildHomepageSchema(),
        );
    }

    /**
     * Generate SEO data for a thread page
     */
    public function forThread(Thread $thread): SeoData
    {
        $title = $thread->meta_title ?: $thread->title;
        $description = $thread->meta_description ?: Str::limit(strip_tags($thread->body), 160);
        $canonical = route('thread.show', [$thread->forum, $thread]);

        $breadcrumbs = [
            ['name' => 'Forums', 'url' => route('thread.index')],
            ['name' => $thread->forum->name, 'url' => route('forum.show', $thread->forum)],
            ['name' => $thread->title],
        ];

        return new SeoData(
            title: $title,
            description: $description,
            keywords: $thread->keywords,
            canonical: $canonical,
            ogTitle: $thread->title,
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'article',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildThreadSchema($thread, $canonical),
        );
    }

    /**
     * Generate SEO data for a forum page
     */
    public function forForum(Forum $forum, int $page = 1): SeoData
    {
        $title = "{$forum->name} Forum";
        $description = $forum->description ?: "Discuss {$forum->name} topics with the OhioChatter community. Join the conversation and share your thoughts.";
        $canonical = route('forum.show', $forum);

        if ($page > 1) {
            $title .= " - Page {$page}";
            $canonical .= "?page={$page}";
        }

        $breadcrumbs = [
            ['name' => 'Forums', 'url' => route('thread.index')],
            ['name' => $forum->name],
        ];

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: "{$forum->name} - OhioChatter Forum",
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'website',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildForumSchema($forum),
        );
    }

    /**
     * Build breadcrumb schema for JSON-LD
     */
    protected function buildBreadcrumbs(array $items): array
    {
        $breadcrumbs = [['name' => 'Home', 'url' => $this->siteUrl]];

        return array_merge($breadcrumbs, $items);
    }

    protected function buildBreadcrumbSchema(array $breadcrumbs): array
    {
        $items = [];

        foreach ($breadcrumbs as $position => $crumb) {
            $item = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $crumb['name'],
            ];

            if (isset($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }

            $items[] = $item;
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    protected function buildHomepageSchema(): array
    {
        return [
            $this->buildOrganizationSchema(),
            [
                '@type' => 'WebSite',
                'name' => $this->siteName,
                'url' => $this->siteUrl,
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $this->siteUrl . '/search?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ];
    }

    protected function buildOrganizationSchema(): array
    {
        return [
            '@type' => 'Organization',
            'name' => $this->siteName,
            'url' => $this->siteUrl,
            'logo' => $this->absoluteUrl('/images/logo.png'),
        ];
    }

    protected function buildThreadSchema(Thread $thread, string $canonical): array
    {
        $schema = [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Forums', 'url' => route('thread.index')],
                ['name' => $thread->forum->name, 'url' => route('forum.show', $thread->forum)],
                ['name' => $thread->title],
            ])),
            [
                '@type' => 'DiscussionForumPosting',
                'headline' => $thread->title,
                'text' => Str::limit(strip_tags($thread->body), 500),
                'url' => $canonical,
                'dateCreated' => $thread->created_at->toIso8601String(),
                'datePublished' => $thread->created_at->toIso8601String(),
                'dateModified' => $thread->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $thread->owner->name ?? 'Anonymous',
                ],
                'interactionStatistic' => [
                    '@type' => 'InteractionCounter',
                    'interactionType' => 'https://schema.org/CommentAction',
                    'userInteractionCount' => $thread->replies_count ?? $thread->replies()->count(),
                ],
            ],
        ];

        return $schema;
    }

    protected function buildForumSchema(Forum $forum): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Forums', 'url' => route('thread.index')],
                ['name' => $forum->name],
            ])),
            [
                '@type' => 'CollectionPage',
                'name' => $forum->name,
                'description' => $forum->description,
                'url' => route('forum.show', $forum),
            ],
        ];
    }

    protected function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->siteUrl, '/') . '/' . ltrim($path, '/');
    }
}
