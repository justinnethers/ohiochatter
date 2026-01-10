<?php

namespace App\Modules\SpamProtection\Commands;

use App\Modules\SpamProtection\Models\RegistrationAttempt;
use Illuminate\Console\Command;

class CleanupRegistrationLogs extends Command
{
    protected $signature = 'spam:cleanup-logs
                            {--days= : Number of days to retain (default from config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete old registration attempt logs';

    public function handle(): int
    {
        $days = $this->option('days')
            ?? config('spam_protection.logging.retention_days', 30);

        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $count = RegistrationAttempt::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info("No registration logs older than {$days} days found.");

            return 0;
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Would delete {$count} registration logs older than {$days} days.");

            $breakdown = RegistrationAttempt::where('created_at', '<', $cutoffDate)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $this->table(
                ['Status', 'Count'],
                collect($breakdown)->map(fn ($c, $s) => [$s, $c])->values()->toArray()
            );

            return 0;
        }

        if (!$this->confirm("This will delete {$count} registration logs older than {$days} days. Continue?")) {
            $this->info('Operation cancelled.');

            return 0;
        }

        $deleted = RegistrationAttempt::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deleted} registration attempt logs older than {$days} days.");

        return 0;
    }
}
