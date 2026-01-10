<?php

namespace App\Modules\SpamProtection\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StopForumSpamChecker
{
    protected string $apiUrl = 'https://api.stopforumspam.org/api';
    protected int $confidenceThreshold;
    protected int $cacheMinutes;

    public function __construct()
    {
        $this->confidenceThreshold = config('spam_protection.stopforumspam.confidence_threshold', 65);
        $this->cacheMinutes = config('spam_protection.stopforumspam.cache_minutes', 60);
    }

    public function check(string $email, string $ip, string $username): array
    {
        // Check cache first to avoid repeated API calls
        $cacheKey = $this->getCacheKey($email, $ip, $username);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get($this->apiUrl, [
                'email' => $email,
                'ip' => $ip,
                'username' => $username,
                'json' => true,
                'confidence' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('StopForumSpam API returned non-success status', [
                    'status' => $response->status(),
                    'email' => $email,
                ]);

                // Fail open - allow registration if API has issues
                return ['passed' => true, 'api_error' => true];
            }

            $data = $response->json();
            $result = $this->analyzeResponse($data);

            // Cache the result
            Cache::put($cacheKey, $result, now()->addMinutes($this->cacheMinutes));

            return $result;
        } catch (\Exception $e) {
            Log::error('StopForumSpam check failed', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            // Fail open - allow registration if API is down
            return ['passed' => true, 'exception' => $e->getMessage()];
        }
    }

    protected function analyzeResponse(array $data): array
    {
        $reasons = [];
        $maxConfidence = 0.0;

        // Check email
        if (isset($data['email']) && ($data['email']['appears'] ?? 0) == 1) {
            $confidence = (float) ($data['email']['confidence'] ?? 0);
            $maxConfidence = max($maxConfidence, $confidence);

            if ($confidence >= $this->confidenceThreshold) {
                $reasons[] = "Email flagged ({$confidence}% confidence)";
            }
        }

        // Check IP
        if (isset($data['ip']) && ($data['ip']['appears'] ?? 0) == 1) {
            $confidence = (float) ($data['ip']['confidence'] ?? 0);
            $maxConfidence = max($maxConfidence, $confidence);

            if ($confidence >= $this->confidenceThreshold) {
                $reasons[] = "IP address flagged ({$confidence}% confidence)";
            }
        }

        // Check username
        if (isset($data['username']) && ($data['username']['appears'] ?? 0) == 1) {
            $confidence = (float) ($data['username']['confidence'] ?? 0);
            $maxConfidence = max($maxConfidence, $confidence);

            if ($confidence >= $this->confidenceThreshold) {
                $reasons[] = "Username flagged ({$confidence}% confidence)";
            }
        }

        if (!empty($reasons)) {
            return [
                'passed' => false,
                'reason' => 'Identified as potential spam: ' . implode(', ', $reasons),
                'confidence' => $maxConfidence,
                'raw_data' => $data,
            ];
        }

        return [
            'passed' => true,
            'confidence' => $maxConfidence,
            'raw_data' => $data,
        ];
    }

    protected function getCacheKey(string $email, string $ip, string $username): string
    {
        return 'sfs_check:' . md5($email . $ip . $username);
    }
}
