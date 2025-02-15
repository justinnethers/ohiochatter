<?php

namespace App\Providers;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Observers\ReplyObserver;
use App\Observers\ThreadObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        Thread::observe(ThreadObserver::class);
        Reply::observe(ReplyObserver::class);
    }
}
