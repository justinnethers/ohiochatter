<?php

namespace App\Modules\SpamProtection\Filament\Widgets;

use App\Modules\SpamProtection\Models\RegistrationAttempt;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpamProtectionStatsWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $today = today();
        $thisWeek = now()->startOfWeek();

        $blockedToday = RegistrationAttempt::where('status', '!=', 'success')
            ->whereDate('created_at', $today)
            ->count();

        $successToday = RegistrationAttempt::where('status', 'success')
            ->whereDate('created_at', $today)
            ->count();

        $blockRate = $this->calculateBlockRate($thisWeek);

        $topBlockReason = RegistrationAttempt::where('status', '!=', 'success')
            ->where('created_at', '>=', $thisWeek)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->value('status');

        return [
            Stat::make('Blocked Today', $blockedToday)
                ->description('Registration attempts blocked')
                ->color('danger')
                ->icon('heroicon-o-shield-exclamation'),

            Stat::make('Successful Registrations', $successToday)
                ->description('Today')
                ->color('success')
                ->icon('heroicon-o-user-plus'),

            Stat::make('Block Rate (7 days)', $blockRate . '%')
                ->description('Percentage of blocked attempts')
                ->color($blockRate > 50 ? 'warning' : 'success')
                ->icon('heroicon-o-chart-pie'),

            Stat::make('Top Block Reason', $this->formatBlockReason($topBlockReason))
                ->description('This week')
                ->icon('heroicon-o-information-circle'),
        ];
    }

    protected function calculateBlockRate($since): float
    {
        $total = RegistrationAttempt::where('created_at', '>=', $since)->count();
        $blocked = RegistrationAttempt::where('status', '!=', 'success')
            ->where('created_at', '>=', $since)
            ->count();

        if ($total === 0) {
            return 0;
        }

        return round(($blocked / $total) * 100, 1);
    }

    protected function formatBlockReason(?string $status): string
    {
        if (!$status) {
            return 'None';
        }

        return match ($status) {
            'blocked_domain' => 'Blocked Domain',
            'blocked_tld' => 'Blocked TLD',
            'blocked_disposable' => 'Disposable Email',
            'blocked_ip_rate' => 'Rate Limited',
            'blocked_pattern' => 'Pattern Detection',
            'blocked_stopforumspam' => 'StopForumSpam',
            'blocked_honeypot' => 'Honeypot',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }
}
