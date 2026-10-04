<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Redirects legacy `/storage/{path}` URLs embedded in old post bodies to the
 * public disk's current URL (e.g. the Laravel Cloud object storage bucket).
 */
class LegacyStorageRedirectController extends Controller
{
    private const CACHE_SECONDS = 31536000;

    /**
     * Permanently redirect to the public disk URL for the given path.
     */
    public function __invoke(Request $request, string $path): RedirectResponse
    {
        if ($this->isUnsafePath($path)) {
            abort(404);
        }

        $target = $this->encodeUnsafeCharacters(Storage::disk('public')->url($path));

        if (rtrim($target, '/') === $request->url()) {
            abort(404);
        }

        return redirect()->away($target, 301)
            ->setPublic()
            ->setMaxAge(self::CACHE_SECONDS)
            ->setSharedMaxAge(self::CACHE_SECONDS);
    }

    /**
     * Percent-encode characters that are invalid in a URL without touching
     * existing escape sequences, so adapters that already encode aren't double-encoded.
     */
    private function encodeUnsafeCharacters(string $url): string
    {
        return preg_replace_callback(
            '/[^A-Za-z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]/',
            fn (array $match): string => rawurlencode($match[0]),
            $url
        );
    }

    private function isUnsafePath(string $path): bool
    {
        $decoded = rawurldecode($path);

        if ($path === '' || str_starts_with($decoded, '/') || str_contains($decoded, '\\')) {
            return true;
        }

        return in_array('..', explode('/', $decoded), true);
    }
}
