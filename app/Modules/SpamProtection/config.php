<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    | Enable or disable individual spam protection features
    */
    'features' => [
        'blocked_domains' => env('SPAM_BLOCKED_DOMAINS', true),
        'blocked_tlds' => env('SPAM_BLOCKED_TLDS', true),
        'disposable_detection' => env('SPAM_DISPOSABLE_DETECTION', true),
        'ip_rate_limiting' => env('SPAM_IP_RATE_LIMITING', true),
        'pattern_detection' => env('SPAM_PATTERN_DETECTION', false),
        'stopforumspam' => env('SPAM_STOPFORUMSPAM', true),
        'captcha' => env('SPAM_CAPTCHA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked Top-Level Domains
    |--------------------------------------------------------------------------
    | Email addresses from these TLDs will be blocked entirely
    */
    'blocked_tlds' => [
        'ru',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_attempts' => env('SPAM_RATE_LIMIT_ATTEMPTS', 3),
        'decay_minutes' => env('SPAM_RATE_LIMIT_DECAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | StopForumSpam Settings
    |--------------------------------------------------------------------------
    */
    'stopforumspam' => [
        'api_key' => env('STOPFORUMSPAM_API_KEY'),
        'confidence_threshold' => env('STOPFORUMSPAM_THRESHOLD', 65),
        'cache_minutes' => env('STOPFORUMSPAM_CACHE_MINUTES', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('SPAM_LOGGING', true),
        'retention_days' => env('SPAM_LOG_RETENTION', 30),
    ],
];
