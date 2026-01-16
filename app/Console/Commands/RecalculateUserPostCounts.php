<?php

namespace App\Console\Commands;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Console\Command;

class RecalculateUserPostCounts extends Command
{
    protected $signature = 'users:recalculate-post-counts
                            {--user= : Recalculate for a specific user ID only}
                            {--dry-run : Show what would be changed without making changes}
                            {--chunk=500 : Number of users to process per chunk}';

    protected $description = 'Recalculate post_count for all users based on their threads and replies';

    public function handle()
    {
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                $this->error("User with ID {$userId} not found.");

                return Command::FAILURE;
            }
            $this->processUser($user, $dryRun);

            return Command::SUCCESS;
        }

        $total = User::count();
        $this->info("Recalculating post counts for {$total} users...");

        if ($dryRun) {
            $this->warn('DRY RUN: No changes will be made.');
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        User::chunkById($chunkSize, function ($users) use ($bar, $dryRun) {
            foreach ($users as $user) {
                $this->processUser($user, $dryRun, false);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Done!');

        return Command::SUCCESS;
    }

    private function processUser(User $user, bool $dryRun, bool $verbose = true): void
    {
        $threadCount = Thread::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $replyCount = Reply::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $newPostCount = $threadCount + $replyCount;

        if ($verbose) {
            $this->info("User {$user->id} ({$user->username}): {$user->post_count} -> {$newPostCount}");
        }

        if (! $dryRun) {
            User::where('id', $user->id)->update(['post_count' => $newPostCount]);
        }
    }
}
