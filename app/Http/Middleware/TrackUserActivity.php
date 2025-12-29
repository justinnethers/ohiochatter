<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            auth()->user()->touchActivity();
        } else {
            $this->trackGuest($request);
        }

        return $next($request);
    }

    protected function trackGuest(Request $request): void
    {
        if ($this->isBot($request)) {
            return;
        }

        $sessionId = $request->session()->getId();
        $cacheKey = 'active_guests';

        $guests = Cache::get($cacheKey, []);
        $guests[$sessionId] = now()->timestamp;

        // Clean expired entries (older than 30 minutes)
        $cutoff = now()->subMinutes(30)->timestamp;
        $guests = array_filter($guests, fn ($time) => $time > $cutoff);

        Cache::put($cacheKey, $guests, now()->addMinutes(30));
    }

    protected function isBot(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $botPatterns = [
            'bot', 'crawl', 'spider', 'slurp', 'googlebot', 'bingbot',
            'yandex', 'baidu', 'duckduck', 'semrush', 'ahrefs', 'mj12bot',
            'dotbot', 'rogerbot', 'facebookexternalhit', 'linkedinbot',
            'twitterbot', 'applebot', 'pingdom', 'uptimerobot', 'headlesschrome',
            'phantomjs', 'curl', 'wget', 'python', 'java/', 'go-http',
            'scrapy', 'httpclient', 'apache-httpclient', 'nutch',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
