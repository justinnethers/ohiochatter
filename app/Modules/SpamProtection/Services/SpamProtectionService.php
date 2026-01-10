<?php

namespace App\Modules\SpamProtection\Services;

use App\Modules\SpamProtection\Models\BlockedEmailDomain;
use App\Modules\SpamProtection\Models\RegistrationAttempt;
use Illuminate\Http\Request;

class SpamProtectionService
{
    protected ?string $blockReason = null;
    protected ?string $blockStatus = null;

    public function __construct(
        protected DisposableEmailChecker $disposableChecker,
        protected PatternDetector $patternDetector,
        protected RateLimiter $rateLimiter,
        protected StopForumSpamChecker $stopForumSpamChecker,
    ) {}

    public function validateRegistration(Request $request, string $email, string $username): bool
    {
        $ip = $request->ip();

        // 1. Check IP rate limiting (fastest check first)
        if ($this->isFeatureEnabled('ip_rate_limiting')) {
            if (!$this->rateLimiter->check($ip)) {
                $this->blockStatus = 'blocked_ip_rate';
                $this->blockReason = 'Too many registration attempts from this IP address. Please try again later.';
                $this->logAttempt($request, $email, $username);

                return false;
            }
        }

        // 2. Check blocked email domains (database lookup)
        if ($this->isFeatureEnabled('blocked_domains')) {
            $blockedDomain = BlockedEmailDomain::getBlockedDomain($email);

            if ($blockedDomain) {
                $this->blockStatus = 'blocked_domain';
                $this->blockReason = 'This email domain has been blocked. Please use a different email address.';
                $this->logAttempt($request, $email, $username);

                return false;
            }
        }

        // 2b. Check blocked TLDs (e.g., .ru, .cn)
        if ($this->isFeatureEnabled('blocked_tlds')) {
            if ($this->isBlockedTld($email)) {
                $this->blockStatus = 'blocked_tld';
                $this->blockReason = 'Email addresses from this domain are not accepted. Please use a different email address.';
                $this->logAttempt($request, $email, $username);

                return false;
            }
        }

        // 3. Check disposable email addresses
        if ($this->isFeatureEnabled('disposable_detection')) {
            if ($this->disposableChecker->isDisposable($email)) {
                $this->blockStatus = 'blocked_disposable';
                $this->blockReason = 'Disposable email addresses are not allowed. Please use a permanent email address.';
                $this->logAttempt($request, $email, $username);

                return false;
            }
        }

        // 4. Check suspicious username patterns
        if ($this->isFeatureEnabled('pattern_detection')) {
            $patternResult = $this->patternDetector->check($username, $email);

            if (!$patternResult['passed']) {
                $this->blockStatus = 'blocked_pattern';
                $this->blockReason = $patternResult['reason'];
                $this->logAttempt($request, $email, $username, $patternResult);

                return false;
            }
        }

        // 5. Check StopForumSpam (external API - most expensive, check last)
        if ($this->isFeatureEnabled('stopforumspam')) {
            $sfsResult = $this->stopForumSpamChecker->check($email, $ip, $username);

            if (!$sfsResult['passed']) {
                $this->blockStatus = 'blocked_stopforumspam';
                $this->blockReason = 'Your registration has been flagged as potentially suspicious. Please contact support if you believe this is an error.';
                $this->logAttempt($request, $email, $username, $sfsResult);

                return false;
            }
        }

        // Log successful attempt for analytics
        $this->blockStatus = 'success';
        $this->logAttempt($request, $email, $username);

        return true;
    }

    public function getBlockReason(): ?string
    {
        return $this->blockReason;
    }

    public function getBlockStatus(): ?string
    {
        return $this->blockStatus;
    }

    protected function isFeatureEnabled(string $feature): bool
    {
        return config("spam_protection.features.{$feature}", true);
    }

    protected function isBlockedTld(string $email): bool
    {
        $blockedTlds = config('spam_protection.blocked_tlds', []);

        if (empty($blockedTlds)) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $tld = strtolower(substr(strrchr($domain, '.'), 1));

        return in_array($tld, array_map('strtolower', $blockedTlds), true);
    }

    protected function logAttempt(Request $request, ?string $email, ?string $username, array $metadata = []): void
    {
        if (!config('spam_protection.logging.enabled', true)) {
            return;
        }

        RegistrationAttempt::create([
            'ip_address' => $request->ip(),
            'email' => $email,
            'username' => $username,
            'user_agent' => $request->userAgent(),
            'status' => $this->blockStatus,
            'block_reason' => $this->blockReason,
            'metadata' => $metadata ?: null,
        ]);
    }
}
