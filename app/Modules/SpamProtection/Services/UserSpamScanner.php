<?php

namespace App\Modules\SpamProtection\Services;

use App\Models\User;
use App\Modules\SpamProtection\Models\BlockedEmailDomain;

class UserSpamScanner
{
    public function __construct(
        protected DisposableEmailChecker $disposableChecker,
        protected StopForumSpamChecker $stopForumSpamChecker,
    ) {}

    public function scan(User $user, bool $skipApi = false): array
    {
        $email = $user->email;
        $username = $user->username;
        $ip = $user->ip_address ?? '0.0.0.0';

        // 1. Check blocked email domains (database lookup)
        if ($this->isFeatureEnabled('blocked_domains')) {
            $blockedDomain = BlockedEmailDomain::getBlockedDomain($email);

            if ($blockedDomain) {
                return [
                    'passed' => false,
                    'status' => 'blocked_domain',
                    'reason' => "Email domain blocked: {$blockedDomain->domain}",
                ];
            }
        }

        // 2. Check blocked TLDs (e.g., .ru, .cn)
        if ($this->isFeatureEnabled('blocked_tlds')) {
            $blockedTld = $this->getBlockedTld($email);
            if ($blockedTld) {
                return [
                    'passed' => false,
                    'status' => 'blocked_tld',
                    'reason' => "Blocked TLD: .{$blockedTld}",
                ];
            }
        }

        // 3. Check disposable email addresses
        if ($this->isFeatureEnabled('disposable_detection')) {
            if ($this->disposableChecker->isDisposable($email)) {
                return [
                    'passed' => false,
                    'status' => 'blocked_disposable',
                    'reason' => 'Disposable email address',
                ];
            }
        }

        // 4. Check StopForumSpam (external API - most expensive, check last)
        if (!$skipApi && $this->isFeatureEnabled('stopforumspam')) {
            $sfsResult = $this->stopForumSpamChecker->check($email, $ip, $username);

            if (!$sfsResult['passed'] && !isset($sfsResult['api_error'])) {
                $confidence = $sfsResult['confidence'] ?? 0;
                return [
                    'passed' => false,
                    'status' => 'blocked_stopforumspam',
                    'reason' => "StopForumSpam flagged ({$confidence}% confidence)",
                ];
            }
        }

        return ['passed' => true];
    }

    protected function isFeatureEnabled(string $feature): bool
    {
        return config("spam_protection.features.{$feature}", true);
    }

    protected function getBlockedTld(string $email): ?string
    {
        $blockedTlds = config('spam_protection.blocked_tlds', []);

        if (empty($blockedTlds)) {
            return null;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $tld = strtolower(substr(strrchr($domain, '.'), 1));

        if (in_array($tld, array_map('strtolower', $blockedTlds), true)) {
            return $tld;
        }

        return null;
    }
}
