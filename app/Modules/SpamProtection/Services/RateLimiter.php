<?php

namespace App\Modules\SpamProtection\Services;

use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    protected int $maxAttempts;
    protected int $decayMinutes;

    public function __construct()
    {
        $this->maxAttempts = config('spam_protection.rate_limit.max_attempts', 3);
        $this->decayMinutes = config('spam_protection.rate_limit.decay_minutes', 60);
    }

    public function check(string $ip): bool
    {
        $key = $this->getCacheKey($ip);
        $attempts = Cache::get($key, 0);

        if ($attempts >= $this->maxAttempts) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($this->decayMinutes));

        return true;
    }

    public function getRemainingAttempts(string $ip): int
    {
        $key = $this->getCacheKey($ip);
        $attempts = Cache::get($key, 0);

        return max(0, $this->maxAttempts - $attempts);
    }

    public function getDecayTime(string $ip): ?int
    {
        $key = $this->getCacheKey($ip);

        return Cache::has($key) ? $this->decayMinutes * 60 : null;
    }

    public function clear(string $ip): void
    {
        Cache::forget($this->getCacheKey($ip));
    }

    protected function getCacheKey(string $ip): string
    {
        return "registration_attempts:{$ip}";
    }
}
