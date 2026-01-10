<?php

namespace App\Modules\SpamProtection\Commands;

use App\Models\User;
use App\Modules\SpamProtection\Services\UserSpamScanner;
use Illuminate\Console\Command;

class ScanUsersForSpam extends Command
{
    protected $signature = 'spam:scan-users
                            {--dry-run : Show what would be flagged without actually flagging}
                            {--limit= : Limit number of users to scan}
                            {--skip-api : Skip StopForumSpam API checks (faster)}
                            {--unflag : Remove spam flags instead of adding them}';

    protected $description = 'Scan existing users for spam indicators and flag them';

    protected array $flaggedUsers = [];

    public function handle(UserSpamScanner $scanner): int
    {
        if ($this->option('unflag')) {
            return $this->handleUnflag();
        }

        $dryRun = $this->option('dry-run');
        $skipApi = $this->option('skip-api');
        $limit = $this->option('limit');

        $this->info($dryRun ? '[DRY RUN] Scanning users for spam...' : 'Scanning users for spam...');

        if ($skipApi) {
            $this->info('Skipping StopForumSpam API checks.');
        }

        $query = User::query()
            ->where('is_flagged_spam', false)
            ->where('is_admin', false)
            ->where('is_moderator', false);

        $totalAvailable = $query->count();
        $limit = $limit ? (int) $limit : null;
        $total = $limit ? min($limit, $totalAvailable) : $totalAvailable;

        if ($total === 0) {
            $this->info('No users to scan.');

            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $flaggedCount = 0;
        $scannedCount = 0;

        $query->chunkById(100, function ($users) use ($scanner, $skipApi, $dryRun, $limit, $bar, &$flaggedCount, &$scannedCount) {
            foreach ($users as $user) {
                // Stop if we've reached the limit
                if ($limit && $scannedCount >= $limit) {
                    return false;
                }

                $result = $scanner->scan($user, $skipApi);
                $scannedCount++;

                if (!$result['passed']) {
                    $flaggedCount++;
                    $this->flaggedUsers[] = [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'reason' => $result['reason'],
                        'status' => $result['status'],
                    ];

                    if (!$dryRun) {
                        $user->update([
                            'is_flagged_spam' => true,
                            'spam_flag_reason' => $result['reason'],
                            'spam_flagged_at' => now(),
                        ]);
                    }
                }

                $bar->advance();

                // Rate limit API calls (1 per second) if using StopForumSpam
                if (!$skipApi) {
                    usleep(100000); // 100ms delay between users
                }
            }

            // Stop chunking if we've reached the limit
            if ($limit && $scannedCount >= $limit) {
                return false;
            }
        });

        $bar->finish();
        $this->newLine(2);

        if ($flaggedCount > 0) {
            $this->displayFlaggedUsers();
        }

        $action = $dryRun ? 'Would flag' : 'Flagged';
        $this->info("{$action} {$flaggedCount} of {$scannedCount} users as spam.");

        if ($dryRun && $flaggedCount > 0) {
            $this->warn('Run without --dry-run to apply these flags.');
        }

        return 0;
    }

    protected function handleUnflag(): int
    {
        $dryRun = $this->option('dry-run');

        $count = User::where('is_flagged_spam', true)->count();

        if ($count === 0) {
            $this->info('No flagged users to unflag.');

            return 0;
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Would unflag {$count} users.");

            $users = User::where('is_flagged_spam', true)
                ->select(['id', 'username', 'email', 'spam_flag_reason'])
                ->limit(20)
                ->get();

            $this->table(
                ['ID', 'Username', 'Email', 'Flag Reason'],
                $users->map(fn ($u) => [$u->id, $u->username, $u->email, $u->spam_flag_reason])->toArray()
            );

            if ($count > 20) {
                $this->info("... and " . ($count - 20) . " more");
            }

            return 0;
        }

        if (!$this->confirm("Are you sure you want to unflag {$count} users?")) {
            $this->info('Operation cancelled.');

            return 0;
        }

        User::where('is_flagged_spam', true)->update([
            'is_flagged_spam' => false,
            'spam_flag_reason' => null,
            'spam_flagged_at' => null,
        ]);

        $this->info("Unflagged {$count} users.");

        return 0;
    }

    protected function displayFlaggedUsers(): void
    {
        $display = array_slice($this->flaggedUsers, 0, 30);

        $this->table(
            ['ID', 'Username', 'Email', 'Reason'],
            array_map(fn ($u) => [
                $u['id'],
                $u['username'],
                strlen($u['email']) > 30 ? substr($u['email'], 0, 27) . '...' : $u['email'],
                strlen($u['reason']) > 40 ? substr($u['reason'], 0, 37) . '...' : $u['reason'],
            ], $display)
        );

        if (count($this->flaggedUsers) > 30) {
            $this->info('... and ' . (count($this->flaggedUsers) - 30) . ' more');
        }

        $this->newLine();

        // Show breakdown by reason
        $byStatus = [];
        foreach ($this->flaggedUsers as $user) {
            $status = $user['status'];
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        }

        $this->info('Breakdown by detection type:');
        $this->table(
            ['Detection Type', 'Count'],
            array_map(fn ($s, $c) => [$this->formatStatus($s), $c], array_keys($byStatus), array_values($byStatus))
        );
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'blocked_domain' => 'Blocked Domain',
            'blocked_tld' => 'Blocked TLD',
            'blocked_disposable' => 'Disposable Email',
            'blocked_pattern' => 'Pattern Detected',
            'blocked_stopforumspam' => 'StopForumSpam',
            default => $status,
        };
    }
}
