<?php

use App\Actions\GenerateSitemap;
use App\Models\Region;
use App\Models\County;
use App\Models\City;
use App\Models\VbForum;
use App\Models\VbThread;

beforeEach(function () {
    // Clean up any existing sitemap files
    $files = [
        'sitemap.xml',
        'sitemap-main.xml',
        'sitemap-locations.xml',
        'sitemap-guides.xml',
        'sitemap-archive.xml',
    ];

    foreach ($files as $file) {
        $path = public_path($file);
        if (file_exists($path)) {
            unlink($path);
        }
    }
});

afterEach(function () {
    // Clean up sitemap files after tests
    $files = [
        'sitemap.xml',
        'sitemap-main.xml',
        'sitemap-locations.xml',
        'sitemap-guides.xml',
        'sitemap-archive.xml',
    ];

    foreach ($files as $file) {
        $path = public_path($file);
        if (file_exists($path)) {
            unlink($path);
        }
    }
});

test('sitemap generation completes without errors', function () {
    // Create minimal test data
    $region = Region::factory()->create(['is_active' => true]);
    $county = County::factory()->create(['region_id' => $region->id]);
    $city = City::factory()->create(['county_id' => $county->id]);

    $action = new GenerateSitemap();
    $action();

    expect(file_exists(public_path('sitemap.xml')))->toBeTrue();
    expect(file_exists(public_path('sitemap-main.xml')))->toBeTrue();
    expect(file_exists(public_path('sitemap-locations.xml')))->toBeTrue();
    expect(file_exists(public_path('sitemap-guides.xml')))->toBeTrue();
    expect(file_exists(public_path('sitemap-archive.xml')))->toBeTrue();
});

test('sitemap handles null updated_at dates gracefully', function () {
    // Create region with null updated_at
    $region = Region::factory()->create([
        'is_active' => true,
        'updated_at' => null,
    ]);

    $county = County::factory()->create([
        'region_id' => $region->id,
        'updated_at' => null,
    ]);

    $city = City::factory()->create([
        'county_id' => $county->id,
        'updated_at' => null,
    ]);

    $action = new GenerateSitemap();

    // Should not throw an exception
    $action();

    expect(file_exists(public_path('sitemap-locations.xml')))->toBeTrue();

    $content = file_get_contents(public_path('sitemap-locations.xml'));
    expect($content)->toContain($region->slug);
});

test('archive sitemap uses SEO-friendly URLs with slugs', function () {
    // Create test archive data with a public forum ID (must be in GenerateSitemap whitelist)
    $forum = VbForum::factory()->create([
        'forumid' => 6, // Serious Business - a public forum ID
        'parentid' => 1,
        'displayorder' => 1,
    ]);
    $thread = VbThread::factory()->create([
        'forumid' => $forum->forumid,
        'visible' => 1,
    ]);

    $action = new GenerateSitemap();
    $action();

    $content = file_get_contents(public_path('sitemap-archive.xml'));

    // Verify URLs contain slugs (ID-slug format)
    expect($content)->toContain("/archive/forum/{$forum->getRouteKey()}");
    expect($content)->toContain("/archive/thread/{$thread->getRouteKey()}");
});

test('VbThread generates correct SEO slug', function () {
    $thread = VbThread::factory()->create([
        'title' => 'Test Thread Title Here',
    ]);

    $routeKey = $thread->getRouteKey();

    // Should start with thread ID
    expect($routeKey)->toStartWith((string) $thread->threadid);

    // Should contain a dash separator
    expect($routeKey)->toContain('-');

    // Should be URL-safe (no spaces or special chars except dash)
    expect($routeKey)->toMatch('/^[a-z0-9\-]+$/');

    // Should contain the slugified title
    expect($routeKey)->toContain('test-thread-title-here');
});

test('VbForum generates correct SEO slug', function () {
    $forum = VbForum::factory()->create([
        'title' => 'Serious Business',
    ]);

    $routeKey = $forum->getRouteKey();

    // Should start with forum ID
    expect($routeKey)->toStartWith((string) $forum->forumid);

    // Should contain a dash separator
    expect($routeKey)->toContain('-');

    // Should be URL-safe
    expect($routeKey)->toMatch('/^[a-z0-9\-]+$/');

    // Should contain the slugified title
    expect($routeKey)->toContain('serious-business');
});

test('VbThread resolves from SEO-friendly route key', function () {
    $thread = VbThread::factory()->create();

    $routeKey = $thread->getRouteKey();

    $resolved = (new VbThread())->resolveRouteBinding($routeKey);

    expect($resolved)->not->toBeNull();
    expect($resolved->threadid)->toBe($thread->threadid);
});

test('VbThread resolves from bare ID for backwards compatibility', function () {
    $thread = VbThread::factory()->create();

    $resolved = (new VbThread())->resolveRouteBinding((string) $thread->threadid);

    expect($resolved)->not->toBeNull();
    expect($resolved->threadid)->toBe($thread->threadid);
});

test('archive thread route returns 200 with SEO-friendly URL', function () {
    $forum = VbForum::factory()->create(['forumid' => 6]); // Public forum ID
    $thread = VbThread::factory()->create([
        'forumid' => $forum->forumid,
        'visible' => 1,
    ]);

    $response = $this->get("/archive/thread/{$thread->getRouteKey()}");

    $response->assertStatus(200);
});

test('archive thread route redirects bare ID to SEO-friendly URL', function () {
    $forum = VbForum::factory()->create(['forumid' => 7]); // Public forum ID
    $thread = VbThread::factory()->create([
        'forumid' => $forum->forumid,
        'visible' => 1,
    ]);

    $response = $this->get("/archive/thread/{$thread->threadid}");

    $response->assertRedirect("/archive/thread/{$thread->getRouteKey()}");
    $response->assertStatus(301);
});

test('legacy showthread.php URL redirects to archive with slug', function () {
    $forum = VbForum::factory()->create(['forumid' => 8]); // Public forum ID
    $thread = VbThread::factory()->create([
        'forumid' => $forum->forumid,
        'title' => 'Test Thread Title',
        'visible' => 1,
    ]);

    // Simulate old vBulletin URL format
    $response = $this->get("/forum/showthread.php?{$thread->threadid}-Test-Thread-Title");

    $response->assertStatus(301);
    $response->assertRedirect("/archive/thread/{$thread->threadid}-Test-Thread-Title");
});