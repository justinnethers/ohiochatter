<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSitemap;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    /**
     * Sections written by GenerateSitemap, each served at /sitemap-{section}.xml.
     *
     * @var array<int, string>
     */
    public const SECTIONS = ['main', 'locations', 'guides', 'archive'];

    public function index(): Response
    {
        return $this->serve('sitemap.xml');
    }

    public function show(string $section): Response
    {
        return $this->serve("sitemap-{$section}.xml");
    }

    private function serve(string $file): Response
    {
        $disk = Storage::disk(GenerateSitemap::disk());
        $path = GenerateSitemap::path($file);

        abort_unless($disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
