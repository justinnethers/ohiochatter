<?php

use Illuminate\Support\Facades\DB;

const META_EXTERNAL_AGENT_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 (compatible; meta-externalagent/1.1 (+https://developers.facebook.com/docs/sharing/webmasters/crawler))';

/**
 * @return array<int, string>
 */
function robotsTxtFullySiteBlockedAgents(): array
{
    $contents = file_get_contents(public_path('robots.txt'));
    $blocks = preg_split('/\R\s*\R/', trim($contents));
    $blocked = [];

    foreach ($blocks as $block) {
        $agents = [];
        $blocksWholeSite = false;

        foreach (preg_split('/\R/', $block) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^user-agent:\s*(.+)$/i', $line, $matches)) {
                $agents[] = strtolower(trim($matches[1]));
            } elseif (preg_match('/^disallow:\s*(.*)$/i', $line, $matches) && trim($matches[1]) === '/') {
                $blocksWholeSite = true;
            }
        }

        if ($blocksWholeSite) {
            $blocked = array_merge($blocked, $agents);
        }
    }

    sort($blocked);

    return array_values(array_unique($blocked));
}

beforeEach(function () {
    $this->withoutVite();
});

dataset('blocked crawler user agents', [
    'meta-externalagent' => [META_EXTERNAL_AGENT_UA],
    'GPTBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)'],
    'ClaudeBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)'],
    'CCBot' => ['CCBot/2.0 (https://commoncrawl.org/faq/)'],
    'SemrushBot' => ['Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)'],
    'AhrefsBot' => ['Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)'],
    'MJ12bot' => ['Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)'],
    'DotBot' => ['Mozilla/5.0 (compatible; DotBot/1.2; +https://opensiteexplorer.org/dotbot; help@moz.com)'],
    'Amazonbot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amazonbot/0.1; +https://developer.amazon.com/support/amazonbot) Chrome/119.0.6045.214 Safari/537.36'],
    'Bytespider' => ['Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; spider-feedback@bytedance.com)'],
    'PetalBot' => ['Mozilla/5.0 (compatible; PetalBot;+https://webmaster.petalsearch.com/site/petalbot)'],
    'DataForSeoBot' => ['Mozilla/5.0 (compatible; DataForSeoBot/1.0; +https://dataforseo.com/dataforseo-bot)'],
]);

dataset('allowed crawler user agents', [
    'Googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'],
    'Mediapartners-Google' => ['Mediapartners-Google'],
    'AdsBot-Google' => ['AdsBot-Google (+http://www.google.com/adsbot.html)'],
    'Bingbot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36'],
    'Applebot (not Applebot-Extended)' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)'],
    'DuckDuckBot' => ['DuckDuckBot/1.1; (+http://duckduckgo.com/duckduckbot.html)'],
    'facebookexternalhit' => ['facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'],
    'OAI-SearchBot (not GPTBot)' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; OAI-SearchBot/1.0; +https://openai.com/searchbot'],
    'ChatGPT-User (not GPTBot)' => ['ChatGPT-User'],
    'Claude-SearchBot (not ClaudeBot)' => ['Claude-SearchBot'],
    'Claude-User (not ClaudeBot)' => ['Claude-User'],
    'PerplexityBot' => ['PerplexityBot'],
    'YandexBot' => ['Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)'],
    'regular Chrome browser' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'],
    'empty user agent' => [''],
]);

it('rejects blocked crawlers with a 403 and no cookies', function (string $userAgent) {
    foreach (['/', '/archive'] as $path) {
        $response = $this->withHeader('User-Agent', $userAgent)->get($path);

        $response->assertStatus(403);
        expect($response->headers->getCookies())->toBeEmpty();
        expect($response->headers->get('Cache-Control'))->toContain('no-store');
        expect($response->headers->get('Content-Type'))->toContain('text/plain');
        expect($response->getContent())->not->toBeEmpty()->not->toContain('<html');
        expect(strlen($response->getContent()))->toBeLessThan(200);
    }
})->with('blocked crawler user agents');

it('matches blocked crawler names case-insensitively', function () {
    $response = $this->withHeader('User-Agent', 'mozilla/5.0 (compatible; semrushbot/7~bl; +http://www.semrush.com/bot.html)')
        ->get('/');

    $response->assertStatus(403);
});

it('lets allowed crawlers and ordinary visitors through', function (string $userAgent) {
    $response = $this->withHeader('User-Agent', $userAgent)->get('/');

    expect($response->getStatusCode())->not->toBe(403);
})->with('allowed crawler user agents');

it('keeps robots.txt reachable for blocked crawlers', function () {
    $response = $this->withHeader('User-Agent', META_EXTERNAL_AGENT_UA)->get('/robots.txt');

    expect($response->getStatusCode())->not->toBe(403);
});

it('blocks exactly the crawlers that robots.txt disallows site-wide', function () {
    $configured = array_map('strtolower', config('crawlers.blocked_user_agents') ?? []);
    sort($configured);

    expect($configured)->not->toBeEmpty();
    expect(robotsTxtFullySiteBlockedAgents())->toBe(array_values(array_unique($configured)));
});

it('rejects blocked crawlers without touching the database', function () {
    DB::enableQueryLog();
    DB::flushQueryLog();

    $response = $this->withHeader('User-Agent', META_EXTERNAL_AGENT_UA)->get('/');

    $response->assertStatus(403);
    expect(DB::getQueryLog())->toBeEmpty();
    expect($response->headers->getCookies())->toBeEmpty();
});
