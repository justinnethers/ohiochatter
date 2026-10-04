<?php

beforeEach(function () {
    config(['filesystems.disks.public.url' => 'https://bucket.example.com']);
});

test('guests requesting a legacy storage URL are permanently redirected to the bucket', function () {
    $response = $this->get('/storage/images/abc123.jpg');

    $response->assertStatus(301);
    $response->assertRedirect('https://bucket.example.com/images/abc123.jpg');
});

test('nested legacy storage paths are preserved in the redirect', function () {
    $response = $this->get('/storage/avatars/archive/gonzo.jpg');

    $response->assertStatus(301);
    $response->assertRedirect('https://bucket.example.com/avatars/archive/gonzo.jpg');
});

test('legacy storage paths with encoded spaces are not double encoded', function () {
    $response = $this->get('/storage/images/my%20pic.jpg');

    $response->assertStatus(301);

    $location = $response->headers->get('Location');
    expect($location)->toEndWith('images/my%20pic.jpg')
        ->and($location)->not->toContain('%2520');
});

test('legacy storage redirects are cacheable by browsers and shared caches for a year', function () {
    $response = $this->get('/storage/images/abc123.jpg');

    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('public')
        ->and($cacheControl)->toContain('max-age=31536000')
        ->and($cacheControl)->toContain('s-maxage=31536000');
});

test('legacy storage redirects do not set cookies so they stay cacheable', function () {
    $response = $this->get('/storage/images/abc123.jpg');

    expect($response->headers->getCookies())->toBeEmpty();
    expect($response->headers->has('set-cookie'))->toBeFalse();
});

test('legacy storage URL returns 404 instead of redirecting when the disk URL points back at /storage', function () {
    config(['filesystems.disks.public.url' => config('app.url').'/storage']);

    $response = $this->get('/storage/images/missing.jpg');

    $response->assertNotFound();
    expect($response->headers->has('Location'))->toBeFalse();
});

test('legacy storage URL rejects path traversal attempts', function (string $uri) {
    $response = $this->get($uri);

    $response->assertNotFound();
    expect($response->headers->has('Location'))->toBeFalse();
})->with([
    'encoded slashes' => '/storage/images/..%2F..%2F.env',
    'encoded dots' => '/storage/images/%2e%2e/%2e%2e/.env',
    'encoded dots and slashes' => '/storage/images/%2e%2e%2F%2e%2e%2F.env',
    'leading traversal' => '/storage/..%2F.env',
    'plain dots' => '/storage/images/../../.env',
]);
