<?php

/**
 * @return array<int, array{agents: array<int, string>, disallow: array<int, string>}>
 */
function parseRobotsTxt(): array
{
    $contents = file_get_contents(public_path('robots.txt'));
    $blocks = preg_split('/\R\s*\R/', trim($contents));
    $groups = [];

    foreach ($blocks as $block) {
        $agents = [];
        $disallow = [];

        foreach (preg_split('/\R/', $block) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^user-agent:\s*(.+)$/i', $line, $matches)) {
                $agents[] = strtolower(trim($matches[1]));
            } elseif (preg_match('/^disallow:\s*(.*)$/i', $line, $matches)) {
                $disallow[] = trim($matches[1]);
            }
        }

        if ($agents !== []) {
            $groups[] = ['agents' => $agents, 'disallow' => $disallow];
        }
    }

    return $groups;
}

function robotsGroupFor(string $agent): ?array
{
    foreach (parseRobotsTxt() as $group) {
        if (in_array(strtolower($agent), $group['agents'], true)) {
            return $group;
        }
    }

    return null;
}

it('blocks unwanted crawlers from the entire site', function (string $agent) {
    $group = robotsGroupFor($agent);

    expect($group)->not->toBeNull("{$agent} has no group in robots.txt");
    expect($group['disallow'])->toContain('/');
})->with([
    'meta-externalagent',
    'gptbot',
    'ccbot',
    'claudebot',
    'semrushbot',
    'amazonbot',
    'mj12bot',
    'dotbot',
]);

it('keeps the wildcard group blocking index.php paths without blocking the whole site', function () {
    $group = robotsGroupFor('*');

    expect($group)->not->toBeNull();
    expect($group['disallow'])->toContain('/index.php/');
    expect($group['disallow'])->not->toContain('/');
});

it('does not single out search engines, ad crawlers, social previews, or AI assistants', function (string $agent) {
    expect(robotsGroupFor($agent))->toBeNull("{$agent} should fall through to the * group");
})->with([
    'googlebot',
    'bingbot',
    'applebot',
    'duckduckbot',
    'mediapartners-google',
    'adsbot-google',
    'facebookexternalhit',
    'oai-searchbot',
    'chatgpt-user',
    'claude-searchbot',
    'claude-user',
    'perplexitybot',
]);

it('advertises the sitemap', function () {
    expect(file_get_contents(public_path('robots.txt')))
        ->toContain('Sitemap: https://ohiochatter.com/sitemap.xml');
});
