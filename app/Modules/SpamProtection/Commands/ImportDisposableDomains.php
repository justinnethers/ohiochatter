<?php

namespace App\Modules\SpamProtection\Commands;

use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportDisposableDomains extends Command
{
    protected $signature = 'spam:import-disposable-domains
                            {--force : Overwrite existing entries}
                            {--source= : Custom URL source for domain list}';

    protected $description = 'Import disposable email domains from public lists';

    protected string $defaultSource = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/master/disposable_email_blocklist.conf';

    public function handle(): int
    {
        $source = $this->option('source') ?: $this->defaultSource;

        $this->info('Fetching disposable email domains...');
        $this->info("Source: {$source}");

        try {
            $response = Http::timeout(30)->get($source);

            if (!$response->successful()) {
                $this->error("Failed to fetch domain list. HTTP status: {$response->status()}");

                return 1;
            }

            $domains = collect(explode("\n", $response->body()))
                ->map(fn ($line) => trim(strtolower($line)))
                ->filter(fn ($line) => !empty($line) && !str_starts_with($line, '#'));

            $this->info("Found {$domains->count()} domains to process.");

            $bar = $this->output->createProgressBar($domains->count());
            $bar->start();

            $imported = 0;
            $skipped = 0;

            foreach ($domains as $domain) {
                if ($this->option('force')) {
                    $result = BlockedEmailDomain::updateOrCreate(
                        ['domain' => $domain],
                        [
                            'type' => 'disposable',
                            'reason' => 'Disposable email service',
                            'is_active' => true,
                        ]
                    );

                    if ($result->wasRecentlyCreated) {
                        $imported++;
                    }
                } else {
                    $exists = BlockedEmailDomain::where('domain', $domain)->exists();

                    if (!$exists) {
                        BlockedEmailDomain::create([
                            'domain' => $domain,
                            'type' => 'disposable',
                            'reason' => 'Disposable email service',
                            'is_active' => true,
                        ]);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Import complete!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['New domains imported', $imported],
                    ['Domains skipped (existing)', $skipped],
                    ['Total processed', $domains->count()],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");

            return 1;
        }
    }
}
