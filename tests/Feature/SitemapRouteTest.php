<?php

use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

beforeEach(function () {
    config(['filesystems.sitemap_disk' => 'sitemaps']);
    Storage::fake('sitemaps');
});

test('sitemap index is served from the sitemap disk as xml', function () {
    Storage::disk('sitemaps')->put('sitemaps/sitemap.xml', '<sitemapindex>index</sitemapindex>');

    get('/sitemap.xml')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<sitemapindex>index</sitemapindex>', false);
});

test('each section sitemap is served from the sitemap disk', function (string $section) {
    Storage::disk('sitemaps')->put("sitemaps/sitemap-{$section}.xml", "<urlset>{$section}</urlset>");

    get("/sitemap-{$section}.xml")
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee("<urlset>{$section}</urlset>", false);
})->with(['main', 'locations', 'guides', 'archive']);

test('a sitemap that has not been generated yet returns 404', function () {
    get('/sitemap.xml')->assertNotFound();
});

test('unknown sitemap sections return 404', function () {
    Storage::disk('sitemaps')->put('sitemaps/sitemap-secrets.xml', '<urlset/>');

    get('/sitemap-secrets.xml')->assertNotFound();
});
