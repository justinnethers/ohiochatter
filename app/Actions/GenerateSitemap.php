<?php

namespace App\Actions;

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

    private function generateArchiveSitemap(): void
    {
        // Skip if archive sitemap already exists (it never changes)
        if (file_exists(public_path('sitemap-archive.xml'))) {
            return;
        }

        $sitemap = Sitemap::create()
            ->add(Url::create('/archive')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER));

        // Add archive forums
        VbForum::where('parentid', '>', 0)
            ->where('displayorder', '>', 0)
            ->each(function ($forum) use ($sitemap) {
                $sitemap->add(
                    Url::create("/archive/forum/{$forum->forumid}")
                        ->setPriority(0.4)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
                );
            });

        // Add archive threads
        VbThread::where('visible', 1)
            ->each(function ($thread) use ($sitemap) {
                $sitemap->add(
                    Url::create("/archive/thread/{$thread->threadid}")
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
            ->add('/sitemap-archive.xml')
            ->writeToFile(public_path('sitemap.xml'));
    }
}
