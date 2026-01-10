<?php

namespace App\Modules\SpamProtection;

use App\Modules\SpamProtection\Services\DisposableEmailChecker;
use App\Modules\SpamProtection\Services\PatternDetector;
use App\Modules\SpamProtection\Services\RateLimiter;
use App\Modules\SpamProtection\Services\SpamProtectionService;
use App\Modules\SpamProtection\Services\StopForumSpamChecker;
use Illuminate\Support\ServiceProvider;

class SpamProtectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config.php',
            'spam_protection'
        );

        $this->app->singleton(DisposableEmailChecker::class);
        $this->app->singleton(PatternDetector::class);
        $this->app->singleton(RateLimiter::class);
        $this->app->singleton(StopForumSpamChecker::class);

        $this->app->singleton(SpamProtectionService::class, function ($app) {
            return new SpamProtectionService(
                $app->make(DisposableEmailChecker::class),
                $app->make(PatternDetector::class),
                $app->make(RateLimiter::class),
                $app->make(StopForumSpamChecker::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ImportDisposableDomains::class,
                Commands\CleanupRegistrationLogs::class,
            ]);
        }
    }
}
