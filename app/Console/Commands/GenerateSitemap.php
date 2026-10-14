<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the XML sitemaps on the sitemap disk';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        app(\App\Actions\GenerateSitemap::class)();

        $this->info('Sitemaps generated.');

        return self::SUCCESS;
    }
}
