<?php

namespace App\Console\Commands;

use App\Models\Reply;
use Illuminate\Console\Command;

class BackfillSearchableBody extends Command
{
    protected $signature = 'replies:backfill-searchable-body {--chunk=500}';

    protected $description = 'Backfill the searchable_body column for existing replies';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $reply = new Reply();

        $total = Reply::whereNull('searchable_body')->count();
        $this->info("Backfilling {$total} replies...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Reply::whereNull('searchable_body')
            ->chunkById($chunkSize, function ($replies) use ($bar, $reply) {
                foreach ($replies as $r) {
                    $r->updateQuietly([
                        'searchable_body' => $reply->stripBlockquotes($r->body),
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Done!');

        return Command::SUCCESS;
    }
}
