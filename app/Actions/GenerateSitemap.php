<?php

namespace App\Actions;

use App\Models\Thread;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;


class GenerateSitemap
{
    public function __invoke()
    {
        Sitemap::create()
            ->add(Thread::all())
            ->add(Url::create('/threads')->setPriority(1)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/serious-business')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/sports')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/forums/politics')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/archive')->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER))
            ->writeToFile(public_path('sitemap.xml'));
    }
}
