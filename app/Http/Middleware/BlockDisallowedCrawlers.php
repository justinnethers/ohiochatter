<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses requests from AI-training and SEO scraper crawlers listed in
 * config/crawlers.php before any session, cookie or database work happens.
 */
class BlockDisallowedCrawlers
{
    /**
     * Return a non-cacheable 403 when the user agent matches a blocked crawler.
     *
     * The robots.txt path is always exempt so crawlers can read the rules.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->path() === 'robots.txt') {
            return $next($request);
        }

        $userAgent = Str::lower($request->userAgent() ?? '');

        if ($userAgent === '' || ! $this->isBlockedUserAgent($userAgent)) {
            return $next($request);
        }

        return response('Crawling of this site is not permitted. See /robots.txt', 403, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Determine whether the lowercased user agent contains a blocked token.
     */
    private function isBlockedUserAgent(string $userAgent): bool
    {
        foreach (config('crawlers.blocked_user_agents', []) as $token) {
            if (str_contains($userAgent, Str::lower($token))) {
                return true;
            }
        }

        return false;
    }
}
