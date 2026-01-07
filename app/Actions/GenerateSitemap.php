<?php

namespace App\Actions;

use App\Models\City;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Region;
use App\Models\Thread;
use App\Models\VbForum;
use App\Models\VbThread;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap
{
    public function __invoke()
    {
        $this->generateMainSitemap();
        $this->generateLocationsSitemap();
        $this->generateGuidesSitemap();
        $this->generateArchiveSitemap();
        $this->generateSitemapIndex();
    }

    private function generateMainSitemap(): void
    {
        Sitemap::create()
            ->add(Thread::all())
            ->add(Url::create('/threads')->setPriority(1)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/serious-business')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/sports')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/politics')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->writeToFile(public_path('sitemap-main.xml'));
    }

    private function generateLocationsSitemap(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/ohio')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

        // Add regions
        Region::where('is_active', true)->each(function ($region) use ($sitemap) {
            $url = Url::create("/ohio/{$region->slug}")
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            if ($region->updated_at) {
                $url->setLastModificationDate($region->updated_at);
            }

            $sitemap->add($url);

            // Add counties for this region
            $region->counties->each(function ($county) use ($sitemap, $region) {
                $url = Url::create("/ohio/{$region->slug}/{$county->slug}")
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

                if ($county->updated_at) {
                    $url->setLastModificationDate($county->updated_at);
                }

                $sitemap->add($url);

                // Add cities for this county
                $county->cities->each(function ($city) use ($sitemap, $region, $county) {
                    $url = Url::create("/ohio/{$region->slug}/{$county->slug}/{$city->slug}")
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

                    if ($city->updated_at) {
                        $url->setLastModificationDate($city->updated_at);
                    }

                    $sitemap->add($url);
                });
            });
        });

        $sitemap->writeToFile(public_path('sitemap-locations.xml'));
    }

    private function generateGuidesSitemap(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/ohio/guide')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/ohio/guide/categories')->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

        // Add content categories
        ContentCategory::each(function ($category) use ($sitemap) {
            $sitemap->add(
                Url::create("/ohio/guide/category/{$category->slug}")
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });

        // Add individual content/guide articles
        Content::published()->each(function ($content) use ($sitemap) {
            $url = Url::create("/ohio/guide/article/{$content->slug}")
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);

            if ($content->updated_at) {
                $url->setLastModificationDate($content->updated_at);
            }

            $sitemap->add($url);
        });

        // Add region guide pages
        Region::where('is_active', true)->each(function ($region) use ($sitemap) {
            $sitemap->add(
                Url::create("/ohio/guide/{$region->slug}")
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );

            // Add region category pages
            $this->addCategoryPagesForLocation($sitemap, "/ohio/guide/{$region->slug}");

            // Add county guide pages
            $region->counties->each(function ($county) use ($sitemap, $region) {
                $sitemap->add(
                    Url::create("/ohio/guide/{$region->slug}/{$county->slug}")
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );

                // Add county category pages
                $this->addCategoryPagesForLocation($sitemap, "/ohio/guide/{$region->slug}/{$county->slug}");

                // Add city guide pages
                $county->cities->each(function ($city) use ($sitemap, $region, $county) {
                    $sitemap->add(
                        Url::create("/ohio/guide/{$region->slug}/{$county->slug}/{$city->slug}")
                            ->setPriority(0.5)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    );

                    // Add city category pages
                    $this->addCategoryPagesForLocation($sitemap, "/ohio/guide/{$region->slug}/{$county->slug}/{$city->slug}");
                });
            });
        });

        $sitemap->writeToFile(public_path('sitemap-guides.xml'));
    }

    private function addCategoryPagesForLocation(Sitemap $sitemap, string $basePath): void
    {
        ContentCategory::each(function ($category) use ($sitemap, $basePath) {
            $sitemap->add(
                Url::create("{$basePath}/category/{$category->slug}")
                    ->setPriority(0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });
    }

    private function generateArchiveSitemap(): void
    {
        // Skip if archive sitemap already exists (it never changes)
        if (file_exists(public_path('sitemap-archive.xml'))) {
            return;
        }

        // Only include publicly accessible forums (must match ArchiveController::PUBLIC_FORUM_IDS)
        $publicForumIds = [
            6, 12, 35, 36,      // Serious Business, Politics, Thread Bomber's Basement, Hall of Fame
            8, 34, 10, 41,      // HS Sports: Football, Scores and Updates, Wrestling, The Rest
            7, 32, 42, 15, 16,  // College and Pro Sports
        ];

        $sitemap = Sitemap::create()
            ->add(Url::create('/archive')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER));

        // Add archive forums with SEO-friendly slugs (only public forums)
        VbForum::whereIn('forumid', $publicForumIds)
            ->each(function ($forum) use ($sitemap) {
                $sitemap->add(
                    Url::create("/archive/forum/{$forum->getRouteKey()}")
                        ->setPriority(0.4)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
                );
            });

        // Add archive threads with SEO-friendly slugs (only from public forums)
        VbThread::where('visible', 1)
            ->whereIn('forumid', $publicForumIds)
            ->each(function ($thread) use ($sitemap) {
                $sitemap->add(
                    Url::create("/archive/thread/{$thread->getRouteKey()}")
                        ->setPriority(0.3)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
                );
            });

        $sitemap->writeToFile(public_path('sitemap-archive.xml'));
    }

    private function generateSitemapIndex(): void
    {
        SitemapIndex::create()
            ->add('/sitemap-main.xml')
            ->add('/sitemap-locations.xml')
            ->add('/sitemap-guides.xml')
            ->add('/sitemap-archive.xml')
            ->writeToFile(public_path('sitemap.xml'));
    }
}
