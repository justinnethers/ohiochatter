<?php

namespace App\Services;

use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Forum;
use App\Models\Region;
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
     * Generate SEO data for a region page
     */
    public function forRegion(Region $region): SeoData
    {
        $title = $region->meta_title ?: "{$region->name} Ohio Guide | Local Community & Resources";
        $description = $region->meta_description ?: "Discover {$region->name}, Ohio. Find local guides, community discussions, restaurants, attractions, and everything you need to know about the {$region->name} area.";
        $canonical = route('region.show', $region);

        $breadcrumbs = [
            ['name' => 'Ohio', 'url' => route('ohio.index')],
            ['name' => $region->name],
        ];

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: "{$region->name}, Ohio - OhioChatter",
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'place',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildRegionSchema($region, $canonical),
        );
    }

    /**
     * Generate SEO data for a county page
     */
    public function forCounty(Region $region, County $county): SeoData
    {
        $title = $county->meta_title ?: "{$county->name} County, Ohio Guide | Communities & Local Resources";
        $description = $county->meta_description ?: "Explore {$county->name} County in {$region->name}, Ohio. Discover cities, local attractions, restaurants, and community resources throughout {$county->name} County.";
        $canonical = route('county.show', [$region, $county]);

        $breadcrumbs = [
            ['name' => 'Ohio', 'url' => route('ohio.index')],
            ['name' => $region->name, 'url' => route('region.show', $region)],
            ['name' => "{$county->name} County"],
        ];

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: "{$county->name} County, Ohio",
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'place',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildCountySchema($region, $county, $canonical),
        );
    }

    /**
     * Generate SEO data for a city page
     */
    public function forCity(Region $region, County $county, City $city): SeoData
    {
        $title = $city->meta_title ?: "{$city->name}, Ohio Guide | Things to Do, Eat & Explore";
        $description = $city->meta_description ?: "Your complete guide to {$city->name}, Ohio in {$county->name} County. Find the best restaurants, attractions, local events, and community resources in {$city->name}.";
        $canonical = route('city.show', [$region, $county, $city]);

        $breadcrumbs = [
            ['name' => 'Ohio', 'url' => route('ohio.index')],
            ['name' => $region->name, 'url' => route('region.show', $region)],
            ['name' => "{$county->name} County", 'url' => route('county.show', [$region, $county])],
            ['name' => $city->name],
        ];

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: "{$city->name}, Ohio",
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'place',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildCitySchema($region, $county, $city, $canonical),
        );
    }

    /**
     * Generate SEO data for content/guide article
     */
    public function forContent(Content $content): SeoData
    {
        $title = $content->meta_title ?: $content->title;
        $description = $content->meta_description ?: $content->excerpt ?: Str::limit(strip_tags($content->body), 160);
        $canonical = route('guide.show', $content);

        $breadcrumbs = $this->buildContentBreadcrumbs($content);
        $image = $content->featured_image
            ? $this->absoluteUrl("/storage/{$content->featured_image}")
            : $this->absoluteUrl($this->defaultImage);

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: $content->title,
            ogDescription: $description,
            ogImage: $image,
            ogType: 'article',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildContentSchema($content, $canonical, $image),
        );
    }

    /**
     * Generate SEO data for category page
     */
    public function forCategory(ContentCategory $category, ?Region $region = null, ?County $county = null, ?City $city = null): SeoData
    {
        $locationName = $this->getLocationName($region, $county, $city);
        $locationPrefix = $locationName ? "{$locationName} " : '';

        $title = $category->meta_title ?: "Best {$category->name} in {$locationPrefix}Ohio";
        $description = $category->meta_description ?: "Find the best {$category->name} in {$locationPrefix}Ohio. Local recommendations, reviews, and guides from the OhioChatter community.";

        $canonical = $this->getCategoryCanonical($category, $region, $county, $city);
        $breadcrumbs = $this->buildCategoryBreadcrumbs($category, $region, $county, $city);

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: $title,
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            ogType: 'website',
            ogUrl: $canonical,
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildCategorySchema($category, $locationName),
        );
    }

    /**
     * Generate SEO data for guide index pages (region/county/city guides)
     */
    public function forGuideIndex(?Region $region = null, ?County $county = null, ?City $city = null): SeoData
    {
        $locationName = $this->getLocationName($region, $county, $city);

        if ($city) {
            $title = "{$city->name}, Ohio Guide | Local Tips & Recommendations";
            $description = "Explore our guides for {$city->name}, Ohio. Local restaurant reviews, things to do, hidden gems, and community recommendations.";
            $canonical = route('guide.city', [$region, $county, $city]);
        } elseif ($county) {
            $title = "{$county->name} County, Ohio Guides | Local Recommendations";
            $description = "Discover the best of {$county->name} County, Ohio. Restaurant guides, local attractions, and community recommendations across the county.";
            $canonical = route('guide.county', [$region, $county]);
        } elseif ($region) {
            $title = "{$region->name} Ohio Guides | Regional Tips & Discoveries";
            $description = "Explore {$region->name}, Ohio with our community guides. Find restaurants, attractions, and local insights from across the region.";
            $canonical = route('guide.region', $region);
        } else {
            $title = 'Ohio Guide | Statewide Tips, Reviews & Local Insights';
            $description = 'Your complete guide to Ohio. Discover restaurants, attractions, hidden gems, and local recommendations from communities across the Buckeye State.';
            $canonical = route('guide.index');
        }

        $breadcrumbs = $this->buildGuideIndexBreadcrumbs($region, $county, $city);

        return new SeoData(
            title: $title,
            description: $description,
            canonical: $canonical,
            ogTitle: $title,
            ogDescription: $description,
            ogImage: $this->absoluteUrl($this->defaultImage),
            breadcrumbs: $breadcrumbs,
            jsonLd: $this->buildGuideIndexSchema($locationName, $canonical),
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

    protected function buildRegionSchema(Region $region, string $canonical): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Ohio', 'url' => route('ohio.index')],
                ['name' => $region->name],
            ])),
            [
                '@type' => 'Place',
                'name' => "{$region->name}, Ohio",
                'description' => $region->description ?: "The {$region->name} region of Ohio",
                'url' => $canonical,
                'containedInPlace' => [
                    '@type' => 'State',
                    'name' => 'Ohio',
                    'containedInPlace' => [
                        '@type' => 'Country',
                        'name' => 'United States',
                    ],
                ],
            ],
        ];
    }

    protected function buildCountySchema(Region $region, County $county, string $canonical): array
    {
        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Ohio', 'url' => route('ohio.index')],
                ['name' => $region->name, 'url' => route('region.show', $region)],
                ['name' => "{$county->name} County"],
            ])),
            [
                '@type' => 'AdministrativeArea',
                'name' => "{$county->name} County, Ohio",
                'description' => $county->description ?: "{$county->name} County in the {$region->name} region of Ohio",
                'url' => $canonical,
                'containedInPlace' => [
                    '@type' => 'State',
                    'name' => 'Ohio',
                ],
            ],
        ];
    }

    protected function buildCitySchema(Region $region, County $county, City $city, string $canonical): array
    {
        $schema = [
            '@type' => 'City',
            'name' => $city->name,
            'description' => $city->description ?: "The city of {$city->name} in {$county->name} County, Ohio",
            'url' => $canonical,
            'containedInPlace' => [
                '@type' => 'AdministrativeArea',
                'name' => "{$county->name} County",
                'containedInPlace' => [
                    '@type' => 'State',
                    'name' => 'Ohio',
                ],
            ],
        ];

        if ($city->population) {
            $schema['population'] = $city->population;
        }

        if ($city->coordinates && isset($city->coordinates['lat'], $city->coordinates['lng'])) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $city->coordinates['lat'],
                'longitude' => $city->coordinates['lng'],
            ];
        }

        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs([
                ['name' => 'Ohio', 'url' => route('ohio.index')],
                ['name' => $region->name, 'url' => route('region.show', $region)],
                ['name' => "{$county->name} County", 'url' => route('county.show', [$region, $county])],
                ['name' => $city->name],
            ])),
            $schema,
        ];
    }

    protected function buildContentSchema(Content $content, string $canonical, string $image): array
    {
        $breadcrumbs = $this->buildContentBreadcrumbs($content);

        $article = [
            '@type' => 'Article',
            'headline' => $content->title,
            'description' => $content->excerpt ?: Str::limit(strip_tags($content->body), 160),
            'url' => $canonical,
            'image' => $image,
            'datePublished' => $content->published_at?->toIso8601String() ?? $content->created_at->toIso8601String(),
            'dateModified' => $content->updated_at->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName,
                'url' => $this->siteUrl,
            ],
        ];

        if ($content->author) {
            $article['author'] = [
                '@type' => 'Person',
                'name' => $content->author->name,
            ];
        }

        return [
            $this->buildBreadcrumbSchema($this->buildBreadcrumbs($breadcrumbs)),
            $article,
        ];
    }

    protected function buildCategorySchema(ContentCategory $category, ?string $locationName): array
    {
        return [
            [
                '@type' => 'CollectionPage',
                'name' => $locationName
                    ? "Best {$category->name} in {$locationName}, Ohio"
                    : "Best {$category->name} in Ohio",
                'description' => $category->description,
            ],
        ];
    }

    protected function buildGuideIndexSchema(?string $locationName, string $canonical): array
    {
        return [
            [
                '@type' => 'CollectionPage',
                'name' => $locationName
                    ? "{$locationName}, Ohio Guide"
                    : 'Ohio Guide',
                'url' => $canonical,
            ],
        ];
    }

    protected function buildContentBreadcrumbs(Content $content): array
    {
        $crumbs = [
            ['name' => 'Guide', 'url' => route('guide.index')],
        ];

        if ($content->locatable) {
            $location = $content->locatable;

            if ($location instanceof City) {
                $county = $location->county;
                $region = $county->region;
                $crumbs[] = ['name' => $region->name, 'url' => route('guide.region', $region)];
                $crumbs[] = ['name' => "{$county->name} County", 'url' => route('guide.county', [$region, $county])];
                $crumbs[] = ['name' => $location->name, 'url' => route('guide.city', [$region, $county, $location])];
            } elseif ($location instanceof County) {
                $region = $location->region;
                $crumbs[] = ['name' => $region->name, 'url' => route('guide.region', $region)];
                $crumbs[] = ['name' => "{$location->name} County", 'url' => route('guide.county', [$region, $location])];
            } elseif ($location instanceof Region) {
                $crumbs[] = ['name' => $location->name, 'url' => route('guide.region', $location)];
            }
        }

        if ($content->contentCategory) {
            $crumbs[] = ['name' => $content->contentCategory->name];
        }

        $crumbs[] = ['name' => $content->title];

        return $crumbs;
    }

    protected function buildCategoryBreadcrumbs(ContentCategory $category, ?Region $region, ?County $county, ?City $city): array
    {
        $crumbs = [
            ['name' => 'Guide', 'url' => route('guide.index')],
        ];

        if ($region) {
            $crumbs[] = ['name' => $region->name, 'url' => route('guide.region', $region)];
        }

        if ($county) {
            $crumbs[] = ['name' => "{$county->name} County", 'url' => route('guide.county', [$region, $county])];
        }

        if ($city) {
            $crumbs[] = ['name' => $city->name, 'url' => route('guide.city', [$region, $county, $city])];
        }

        $crumbs[] = ['name' => $category->name];

        return $crumbs;
    }

    protected function buildGuideIndexBreadcrumbs(?Region $region, ?County $county, ?City $city): array
    {
        $crumbs = [
            ['name' => 'Guide', 'url' => route('guide.index')],
        ];

        if ($region) {
            $crumbs[] = ['name' => $region->name, 'url' => route('guide.region', $region)];
        }

        if ($county) {
            $crumbs[] = ['name' => "{$county->name} County", 'url' => route('guide.county', [$region, $county])];
        }

        if ($city) {
            $crumbs[] = ['name' => $city->name];
        }

        return $crumbs;
    }

    protected function getLocationName(?Region $region, ?County $county, ?City $city): ?string
    {
        if ($city) {
            return $city->name;
        }

        if ($county) {
            return "{$county->name} County";
        }

        if ($region) {
            return $region->name;
        }

        return null;
    }

    protected function getCategoryCanonical(ContentCategory $category, ?Region $region, ?County $county, ?City $city): string
    {
        if ($city) {
            return route('guide.city.category', [$region, $county, $city, $category]);
        }

        if ($county) {
            return route('guide.county.category', [$region, $county, $category]);
        }

        if ($region) {
            return route('guide.region.category', [$region, $category]);
        }

        return route('guide.category', $category);
    }

    protected function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->siteUrl, '/') . '/' . ltrim($path, '/');
    }
}
