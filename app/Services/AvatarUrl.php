<?php

namespace App\Services;

final class AvatarUrl
{
    /**
     * Build a public avatar URL from a path relative to the configured avatar base URL.
     */
    public static function fromPath(string $path): string
    {
        return rtrim((string) config('app.avatar_base_url', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
