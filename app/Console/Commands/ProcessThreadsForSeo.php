<?php

namespace App\Console\Commands;

use App\Actions\Threads\FetchSeoTags;
use App\Models\Thread;
use Illuminate\Console\Command;

class ProcessThreadsForSeo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-threads-for-seo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SEO meta tags via OpenAI for threads flagged with regenerate_meta';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // get the 10 latest threads with regenerate_meta = true
        $threadsToProcess = Thread::latest()
            ->where('regenerate_meta', true)
            ->take(25)
            ->get();

        $threadsToProcess->map(function (Thread $thread) {
            app(FetchSeoTags::class)($thread);
        });
    }
}
