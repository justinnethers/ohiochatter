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

    protected function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->siteUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Build breadcrumb schema for JSON-LD
     */
    protected function buildBreadcrumbs(array $items): array
    {
        $breadcrumbs = [['name' => 'Home', 'url' => $this->siteUrl]];

        return array_merge($breadcrumbs, $items);
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
            $this->buildSiteNavigationSchema(),
        ];
    }

    protected function buildSiteNavigationSchema(): array
    {
        return [
            '@type' => 'SiteNavigationElement',
            'name' => 'Main Navigation',
            'hasPart' => [
                [
                    '@type' => 'SiteNavigationElement',
                    'name' => 'Home',
                    'url' => $this->siteUrl,
                ],
                [
                    '@type' => 'SiteNavigationElement',
                    'name' => 'Serious Business',
                    'url' => $this->siteUrl . '/forum/serious-business',
                ],
                [
                    '@type' => 'SiteNavigationElement',
                    'name' => 'Sports',
                    'url' => $this->siteUrl . '/forum/sports',
                ],
                [
                    '@type' => 'SiteNavigationElement',
                    'name' => 'Forum Archive',
                    'url' => route('archive.index'),
                ],
//                [
//                    '@type' => 'SiteNavigationElement',
//                    'name' => 'BuckEYE Game',
//                    'url' => route('buckeye.index'),
//                ],
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
                    'name' => $thread->owner->username ?? 'Anonymous',
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

    /**
     * Generate SEO data for the BuckEYE game page
     */
    public function forBuckEyeGame(): SeoData
    {
        $description = "Play BuckEYE, Ohio's ultimate daily puzzle game! Test your knowledge of the Buckeye State by identifying pixelated Ohio-themed images. 5 guesses, 5 pixelation levels.";

        return new SeoData(
            title: "BuckEYE - Ohio's Daily Picture Puzzle Game",
            description: $description,
            canonical: route('buckeye.index'),
            ogTitle: "BuckEYE - Ohio's Daily Picture Puzzle Game",
            ogDescription: $description,
            ogImage: $this->absoluteUrl('/images/buckeye-og.jpg'),
            ogType: 'website',
            ogUrl: route('buckeye.index'),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'BuckEYE'],
            ]),
            jsonLd: $this->buildBuckEyeSchema(),
        );
    }

    protected function buildBuckEyeSchema(): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'BuckEYE'],
            ])),
            [
                '@type' => 'WebApplication',
                'name' => 'BuckEYE',
                'description' => "Ohio's daily picture puzzle game. Identify pixelated Ohio-themed images in 5 guesses.",
                'url' => route('buckeye.index'),
                'applicationCategory' => 'Game',
                'operatingSystem' => 'Any',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                ],
            ],
        ];
    }

    /**
     * Generate SEO data for the Wordio game page
     */
    public function forOhioWordle(): SeoData
    {
        $description = "Play Wordio, Ohio's daily word puzzle! Guess the Ohio-themed word in 6 tries. Test your knowledge of the Buckeye State with our word game.";

        return new SeoData(
            title: "Wordio - Ohio's Daily Word Puzzle Game",
            description: $description,
            canonical: route('ohiowordle.index'),
            ogTitle: "Wordio - Ohio's Daily Word Puzzle Game",
            ogDescription: $description,
            ogImage: $this->absoluteUrl('/images/wordio-og.jpg'),
            ogType: 'website',
            ogUrl: route('ohiowordle.index'),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Wordio'],
            ]),
            jsonLd: $this->buildOhioWordleSchema(),
        );
    }

    protected function buildOhioWordleSchema(): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Wordio'],
            ])),
            [
                '@type' => 'WebApplication',
                'name' => 'Wordio',
                'description' => "Ohio's daily word puzzle game. Guess the Ohio-themed word in 6 tries.",
                'url' => route('ohiowordle.index'),
                'applicationCategory' => 'Game',
                'operatingSystem' => 'Any',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                ],
            ],
        ];
    }

    /**
     * Generate SEO data for the search results page
     */
    public function forSearch(string $query, int $resultCount): SeoData
    {
        $title = "Search: {$query}";
        $description = "Search results for \"{$query}\" on OhioChatter. Found {$resultCount} results in forums and discussions.";

        return new SeoData(
            title: $title,
            description: $description,
            canonical: route('search.show', ['q' => $query]),
            robots: 'noindex, follow',
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Search'],
                ['name' => $query],
            ]),
            jsonLd: [
                $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                    ['name' => 'Search'],
                    ['name' => $query],
                ])),
            ],
        );
    }

    /**
     * Generate SEO data for a user profile page
     */
    public function forProfile(\App\Models\User $user): SeoData
    {
        $description = "{$user->username}'s profile on OhioChatter. Member since " . $user->created_at->format('F Y') . ".";

        return new SeoData(
            title: "{$user->username}'s Profile",
            description: $description,
            canonical: route('profile.show', $user),
            ogTitle: "{$user->username} - OhioChatter Member",
            ogDescription: $description,
            ogImage: $user->avatar_url ?? $this->absoluteUrl($this->defaultImage),
            ogType: 'profile',
            ogUrl: route('profile.show', $user),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Members'],
                ['name' => $user->username],
            ]),
            jsonLd: $this->buildProfileSchema($user),
        );
    }

    protected function buildProfileSchema(\App\Models\User $user): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Members'],
                ['name' => $user->username],
            ])),
            [
                '@type' => 'ProfilePage',
                'mainEntity' => [
                    '@type' => 'Person',
                    'name' => $user->username,
                    'url' => route('profile.show', $user),
                ],
            ],
        ];
    }

    /**
     * Generate SEO data for the archive index page
     */
    public function forArchiveIndex(): SeoData
    {
        $description = "Browse the OhioChatter forum archive. Explore classic discussions from Ohio's online community.";

        return new SeoData(
            title: 'Forum Archive',
            description: $description,
            canonical: route('archive.index'),
            ogTitle: 'Forum Archive - OhioChatter',
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'website',
            ogUrl: route('archive.index'),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Archive'],
            ]),
            jsonLd: [
                $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                    ['name' => 'Archive'],
                ])),
                [
                    '@type' => 'CollectionPage',
                    'name' => 'Forum Archive',
                    'description' => $description,
                    'url' => route('archive.index'),
                ],
            ],
        );
    }

    /**
     * Generate SEO data for an archived forum page
     */
    public function forArchiveForum(\App\Models\VbForum $forum): SeoData
    {
        $description = $forum->description ?: "Browse archived threads from {$forum->title} on OhioChatter.";

        return new SeoData(
            title: "{$forum->title} - Forum Archive",
            description: $description,
            canonical: route('archive.forum', $forum),
            ogTitle: "{$forum->title} - Forum Archive",
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'website',
            ogUrl: route('archive.forum', $forum),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Archive', 'url' => route('archive.index')],
                ['name' => $forum->title],
            ]),
            jsonLd: [
                $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                    ['name' => 'Archive', 'url' => route('archive.index')],
                    ['name' => $forum->title],
                ])),
                [
                    '@type' => 'CollectionPage',
                    'name' => $forum->title,
                    'description' => $description,
                    'url' => route('archive.forum', $forum),
                ],
            ],
        );
    }

    /**
     * Generate SEO data for an archived thread page
     */
    public function forArchiveThread(\App\Models\VbThread $thread, $firstPost): SeoData
    {
        $description = Str::limit(strip_tags($firstPost?->pagetext ?? ''), 160);

        return new SeoData(
            title: "{$thread->title} - Forum Archive",
            description: $description,
            canonical: route('archive.thread', $thread),
            ogTitle: $thread->title,
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'article',
            ogUrl: route('archive.thread', $thread),
            breadcrumbs: $this->buildBreadcrumbs([
                ['name' => 'Archive', 'url' => route('archive.index')],
                ['name' => $thread->forum->title ?? 'Forum', 'url' => $thread->forum ? route('archive.forum', $thread->forum) : null],
                ['name' => Str::limit($thread->title, 50)],
            ]),
            jsonLd: $this->buildArchiveThreadSchema($thread, $firstPost),
        );
    }

    protected function buildArchiveThreadSchema(\App\Models\VbThread $thread, $firstPost): array
    {
        $breadcrumbs = [
            ['name' => 'Archive', 'url' => route('archive.index')],
            ['name' => $thread->forum->title ?? 'Forum', 'url' => $thread->forum ? route('archive.forum', $thread->forum) : null],
            ['name' => Str::limit($thread->title, 50)],
        ];

        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs($breadcrumbs)),
            [
                '@type' => 'DiscussionForumPosting',
                'headline' => $thread->title,
                'text' => Str::limit(strip_tags($firstPost?->pagetext ?? ''), 500),
                'url' => route('archive.thread', $thread),
                'dateCreated' => $thread->dateline ? date('c', $thread->dateline) : null,
                'datePublished' => $thread->dateline ? date('c', $thread->dateline) : null,
                'interactionStatistic' => [
                    '@type' => 'InteractionCounter',
                    'interactionType' => 'https://schema.org/CommentAction',
                    'userInteractionCount' => $thread->replycount ?? 0,
                ],
            ],
        ];
    }
}
