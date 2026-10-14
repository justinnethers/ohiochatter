<?php

namespace App\Http\Controllers;

use App\Services\LegacyVbulletinUrlResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sends legacy vBulletin URLs (showthread.php, showpost.php, forumdisplay.php,
 * member.php and the lite archive) straight to their final canonical page with
 * a single 301, or 404s when there is no visible target.
 */
class LegacyVbulletinRedirectController extends Controller
{
    private const SCRIPT_PATTERN = '#^(?:forum/)?(showthread|showpost|forumdisplay|member|archive/index)(?:\.php)?(?:/(.*))?$#s';

    /** Thread/forum/member id with optional slug and optional "/pageN", e.g. "48553-some-slug/page2". */
    private const SLUG_PATTERN = '/^(\d+)(?:-[^\/]*)?(?:\/page(\d+))?\/?$/';

    /** Lite archive file name, e.g. "t-48553.html", "t-48553-p-2.html", "f-6.html". */
    private const LITE_ARCHIVE_PATTERN = '/^(t|f)-(\d+)(?:-p-(\d+))?\.html$/';

    public function __construct(private LegacyVbulletinUrlResolver $resolver) {}

    public function __invoke(Request $request, string $legacyPath): RedirectResponse
    {
        if (! preg_match(self::SCRIPT_PATTERN, $legacyPath, $script)) {
            abort(404);
        }

        $pathInfo = ($script[2] ?? '') === '' ? null : $script[2];
        $slugParts = $this->slugParts($pathInfo ?? $this->firstQuerySegment($request));
        $isBareRequest = $pathInfo === null && $this->rawQuery($request) === '';

        $url = match (true) {
            $isBareRequest && in_array($script[1], ['showthread', 'forumdisplay'], true) => route('archive.index'),
            $script[1] === 'showthread' => $this->showThread($request, $slugParts),
            $script[1] === 'showpost' => $this->showPost($request),
            $script[1] === 'forumdisplay' => $this->forumDisplay($request, $slugParts),
            $script[1] === 'member' => $this->member($request, $slugParts),
            default => $this->liteArchive($request, $pathInfo),
        };

        if ($url === null) {
            abort(404);
        }

        return redirect($url, 301);
    }

    /**
     * @param  array{0: int, 1: ?int}|null  $slugParts
     */
    private function showThread(Request $request, ?array $slugParts): ?string
    {
        $postId = $this->postIdParam($request);
        $threadId = $this->intParam($request, 't');

        if ($postId !== null) {
            $url = $this->resolver->postUrl($postId, $request->user());

            if ($url !== null || $threadId === null) {
                return $url;
            }
        }

        if ($threadId !== null) {
            return $this->threadUrl($request, $threadId, $this->intParam($request, 'page'));
        }

        if ($slugParts !== null) {
            return $this->threadUrl($request, $slugParts[0], $slugParts[1] ?? $this->intParam($request, 'page'));
        }

        return null;
    }

    private function showPost(Request $request): ?string
    {
        $postId = $this->postIdParam($request);

        return $postId === null ? null : $this->resolver->postUrl($postId, $request->user());
    }

    /**
     * @param  array{0: int, 1: ?int}|null  $slugParts
     */
    private function forumDisplay(Request $request, ?array $slugParts): ?string
    {
        $forumId = $this->intParam($request, 'f') ?? $slugParts[0] ?? null;

        return $forumId === null ? null : $this->resolver->forumUrl($forumId, $request->user());
    }

    /**
     * @param  array{0: int, 1: ?int}|null  $slugParts
     */
    private function member(Request $request, ?array $slugParts): ?string
    {
        $vbUserId = $this->intParam($request, 'u') ?? $slugParts[0] ?? null;

        return $vbUserId === null ? null : $this->resolver->memberUrl($vbUserId);
    }

    private function liteArchive(Request $request, ?string $file): ?string
    {
        if ($file === null) {
            return route('archive.index');
        }

        if (! preg_match(self::LITE_ARCHIVE_PATTERN, $file, $match)) {
            return null;
        }

        $id = (int) $match[2];

        if ($match[1] === 'f') {
            return $this->resolver->forumUrl($id, $request->user());
        }

        return $this->resolver->threadUrl(
            $id,
            (int) ($match[3] ?? 1),
            LegacyVbulletinUrlResolver::VB_LITE_ARCHIVE_POSTS_PER_PAGE,
            $request->user(),
        );
    }

    private function threadUrl(Request $request, int $threadId, ?int $vbPage): ?string
    {
        return $this->resolver->threadUrl(
            $threadId,
            $vbPage ?? 1,
            LegacyVbulletinUrlResolver::VB_POSTS_PER_PAGE,
            $request->user(),
        );
    }

    /**
     * Parse "48553-some-slug/page2" into [id, page|null].
     *
     * @return array{0: int, 1: ?int}|null
     */
    private function slugParts(?string $segment): ?array
    {
        if ($segment === null || ! preg_match(self::SLUG_PATTERN, rtrim($segment, '='), $match)) {
            return null;
        }

        return [(int) $match[1], isset($match[2]) ? (int) $match[2] : null];
    }

    /**
     * The first "&"-separated piece of the raw query string. Read from the
     * server variable because getQueryString() normalizes it and encodes "/".
     */
    private function firstQuerySegment(Request $request): ?string
    {
        $segment = explode('&', $this->rawQuery($request), 2)[0];

        return $segment === '' ? null : $segment;
    }

    private function rawQuery(Request $request): string
    {
        return rawurldecode((string) $request->server('QUERY_STRING', ''));
    }

    private function postIdParam(Request $request): ?int
    {
        return $this->intParam($request, 'p') ?? $this->intParam($request, 'postid');
    }

    private function intParam(Request $request, string $key): ?int
    {
        $value = $request->query($key);

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }
}
