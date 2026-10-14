<?php

use App\Models\User;
use App\Models\VbForum;
use App\Models\VbPost;
use App\Models\VbThread;
use App\Models\VbUser;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Legacy vBulletin URL redirects
|--------------------------------------------------------------------------
|
| Old vBulletin URLs (showthread, showpost, forumdisplay, member and the
| lite archive) must 301 straight to the final canonical archive / profile
| URL, or 404. They must never chain and never land on the homepage.
|
| Every URL is exercised under the real prefixes ('' and 'forum/') and,
| where it is a query-string form, under the infrastructure rewrite alias
| ('vb-legacy/' and 'vb-legacy/forum/') with the ".php" stripped.
|
*/

beforeEach(function () {
    config(['scout.driver' => null]);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Build a request path for a legacy URL template under the given prefix.
 * The vb-legacy alias receives the URL with ".php" stripped, as the
 * infrastructure rewrite does.
 */
function legacyUrl(string $prefix, string $template, array $replacements = []): string
{
    $path = str_replace(
        array_map(fn (string $key) => '{'.$key.'}', array_keys($replacements)),
        array_values($replacements),
        $template
    );

    if (str_starts_with($prefix, 'vb-legacy/')) {
        $path = preg_replace('/\.php/', '', $path, 1);
    }

    return '/'.$prefix.$path;
}

function legacyForum(int $forumId = 6): VbForum
{
    return VbForum::query()->find($forumId)
        ?? VbForum::factory()->create(['forumid' => $forumId, 'title' => 'Legacy Forum '.$forumId]);
}

function legacyThread(int $forumId = 6, array $attributes = []): VbThread
{
    $forum = legacyForum($forumId);

    return VbThread::factory()->create(array_merge([
        'forumid' => $forum->forumid,
        'title' => 'Some Legacy Thread Title',
        'visible' => 1,
    ], $attributes));
}

/**
 * Create a thread with $postCount posts. Datelines strictly increase with
 * creation order unless $sameDateline is true.
 */
function legacyThreadWithPosts(int $postCount, int $forumId = 6, bool $sameDateline = false): VbThread
{
    $thread = legacyThread($forumId, ['replycount' => max(0, $postCount - 1)]);

    $base = 1_100_000_000;
    $rows = [];

    for ($i = 0; $i < $postCount; $i++) {
        $rows[] = [
            'threadid' => $thread->threadid,
            'userid' => 1,
            'username' => 'poster',
            'dateline' => $sameDateline ? $base : $base + ($i * 60),
            'pagetext' => 'Post number '.($i + 1),
            'visible' => 1,
        ];
    }

    foreach (array_chunk($rows, 100) as $chunk) {
        VbPost::query()->insert($chunk);
    }

    return $thread;
}

/**
 * The post sitting at a zero-based chronological position in the thread.
 */
function legacyPostAt(VbThread $thread, int $position): VbPost
{
    return VbPost::query()
        ->where('threadid', $thread->threadid)
        ->orderBy('dateline')
        ->orderBy('postid')
        ->skip($position)
        ->firstOrFail();
}

function legacyArchiveThreadUrl(VbThread $thread, int $page = 1): string
{
    return route('archive.thread', ['thread' => $thread] + ($page > 1 ? ['page' => $page] : []));
}

function legacyArchiveForumUrl(VbForum $forum, int $page = 1): string
{
    return route('archive.forum', ['forum' => $forum] + ($page > 1 ? ['page' => $page] : []));
}

function legacyAssertRedirectsTo(string $url, string $expected): void
{
    get($url)
        ->assertStatus(301)
        ->assertRedirect($expected);
}

function legacyAssertNotFound(string $url): void
{
    get($url)->assertNotFound();
}

/**
 * Build the URL and expected target for one kind of legacy entity living in
 * the given forum.
 *
 * @return array{0: string, 1: string} [legacy path, expected canonical URL]
 */
function legacyScenario(string $kind, int $forumId, bool $hidden = false): array
{
    $thread = legacyThread($forumId, ['visible' => $hidden ? 0 : 1]);

    return match ($kind) {
        'thread' => ['/forum/showthread.php?t='.$thread->threadid, legacyArchiveThreadUrl($thread)],
        'post' => [
            '/forum/showpost.php?p='.VbPost::factory()->create([
                'threadid' => $thread->threadid,
                'dateline' => 1_100_000_000,
            ])->postid,
            legacyArchiveThreadUrl($thread),
        ],
        'forum' => ['/forum/forumdisplay.php?f='.$forumId, legacyArchiveForumUrl(VbForum::find($forumId))],
    };
}

/*
|--------------------------------------------------------------------------
| Datasets (strings only; evaluated before the app boots)
|--------------------------------------------------------------------------
*/

dataset('legacy any prefix', [
    'root' => '',
    'forum/' => 'forum/',
    'vb-legacy alias' => 'vb-legacy/',
    'vb-legacy/forum alias' => 'vb-legacy/forum/',
]);

dataset('legacy real prefix', [
    'root' => '',
    'forum/' => 'forum/',
]);

dataset('legacy thread query forms', [
    't=' => 'showthread.php?t={id}',
    'bare id-slug' => 'showthread.php?{id}-some-slug',
    'bare id' => 'showthread.php?{id}',
    'bare id with trailing equals' => 'showthread.php?{id}=',
]);

dataset('legacy thread path-info forms', [
    'path-info id-slug' => 'showthread.php/{id}-some-slug',
    'path-info id only' => 'showthread.php/{id}',
    'lite archive t-N.html' => 'archive/index.php/t-{id}.html',
]);

dataset('legacy thread query page forms', [
    't= page 1' => ['showthread.php?t={id}&page=1', 1],
    't= page 3 (posts 31-45)' => ['showthread.php?t={id}&page=3', 2],
    't= page 4 (posts 46-60 start on archive page 2)' => ['showthread.php?t={id}&page=4', 2],
    't= page 5 (posts 61+ start on archive page 3)' => ['showthread.php?t={id}&page=5', 3],
    't= page 99 clamps to last page' => ['showthread.php?t={id}&page=99', 3],
    't= page 0' => ['showthread.php?t={id}&page=0', 1],
    't= page -2' => ['showthread.php?t={id}&page=-2', 1],
    't= page abc' => ['showthread.php?t={id}&page=abc', 1],
    'id-slug&page=3' => ['showthread.php?{id}-some-slug&page=3', 2],
    'id-slug&page=99' => ['showthread.php?{id}-some-slug&page=99', 3],
    'id-slug/page3' => ['showthread.php?{id}-some-slug/page3', 2],
    'id-slug/page99' => ['showthread.php?{id}-some-slug/page99', 3],
]);

dataset('legacy thread path-info page forms', [
    'path-info id-slug/page1' => ['showthread.php/{id}-some-slug/page1', 1],
    'path-info id-slug/page3' => ['showthread.php/{id}-some-slug/page3', 2],
    'path-info id-slug/page99' => ['showthread.php/{id}-some-slug/page99', 3],
    'path-info id/page3' => ['showthread.php/{id}/page3', 2],
    'lite archive p-1' => ['archive/index.php/t-{id}-p-1.html', 1],
    'lite archive p-2 clamps to last page' => ['archive/index.php/t-{id}-p-2.html', 3],
]);

dataset('legacy post forms', [
    'showthread.php?p=' => 'showthread.php?p={id}',
    'showthread.php?postid=' => 'showthread.php?postid={id}',
    'showpost.php?p=' => 'showpost.php?p={id}',
    'showpost.php?postid=' => 'showpost.php?postid={id}',
]);

dataset('legacy forum query forms', [
    'f=' => 'forumdisplay.php?f={id}',
    'bare id-slug' => 'forumdisplay.php?{id}-some-slug',
    'f= with page' => 'forumdisplay.php?f={id}&page=3',
    'id-slug with page' => 'forumdisplay.php?{id}-some-slug&page=3',
]);

dataset('legacy forum path-info forms', [
    'path-info id-slug' => 'forumdisplay.php/{id}-some-slug',
    'path-info id-slug/page3' => 'forumdisplay.php/{id}-some-slug/page3',
    'lite archive f-N.html' => 'archive/index.php/f-{id}.html',
    'lite archive f-N-p-3.html' => 'archive/index.php/f-{id}-p-3.html',
]);

dataset('legacy member query forms', [
    'u=' => 'member.php?u={id}',
    'bare id-name' => 'member.php?{id}-some-name',
]);

dataset('legacy entity kinds', ['thread', 'post', 'forum']);

dataset('legacy thread bound entity kinds', ['thread', 'post']);

/*
|--------------------------------------------------------------------------
| Threads
|--------------------------------------------------------------------------
*/

test('legacy thread query-string URLs 301 to the slugged archive thread', function (string $template, string $prefix) {
    $thread = legacyThread();

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $thread->threadid]),
        legacyArchiveThreadUrl($thread)
    );
})->with('legacy thread query forms')->with('legacy any prefix');

test('legacy thread path-info and lite archive URLs 301 to the slugged archive thread', function (string $template, string $prefix) {
    $thread = legacyThread();

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $thread->threadid]),
        legacyArchiveThreadUrl($thread)
    );
})->with('legacy thread path-info forms')->with('legacy real prefix');

test('legacy thread redirects point directly at the final archive URL so no chain remains', function () {
    $this->withoutVite();
    $thread = legacyThread(6, ['threadid' => 48553]);

    $response = get('/forum/showthread.php?48553');

    $response->assertStatus(301);
    expect($response->headers->get('Location'))->toBe(legacyArchiveThreadUrl($thread));

    get($response->headers->get('Location'))->assertOk();
});

test('legacy thread URLs convert vBulletin pages to archive pages and clamp to the last page', function (string $template, int $expectedPage, string $prefix) {
    $thread = legacyThreadWithPosts(60);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $thread->threadid]),
        legacyArchiveThreadUrl($thread, $expectedPage)
    );
})->with('legacy thread query page forms')->with('legacy any prefix');

test('legacy thread path-info and lite archive pages convert to archive pages', function (string $template, int $expectedPage, string $prefix) {
    $thread = legacyThreadWithPosts(60);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $thread->threadid]),
        legacyArchiveThreadUrl($thread, $expectedPage)
    );
})->with('legacy thread path-info page forms')->with('legacy real prefix');

test('lite archive page 2 of a 260 post thread lands on archive page 11', function (string $prefix) {
    $thread = legacyThreadWithPosts(260);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, 'archive/index.php/t-{id}-p-2.html', ['id' => $thread->threadid]),
        legacyArchiveThreadUrl($thread, 11)
    );
})->with('legacy real prefix');

test('lite archive page beyond the end of a 260 post thread clamps to archive page 11', function () {
    $thread = legacyThreadWithPosts(260);

    legacyAssertRedirectsTo(
        '/archive/index.php/t-'.$thread->threadid.'-p-99.html',
        legacyArchiveThreadUrl($thread, 11)
    );
});

test('vBulletin page on a thread with no posts redirects to archive page 1', function () {
    $thread = legacyThread();

    legacyAssertRedirectsTo(
        '/forum/showthread.php?t='.$thread->threadid.'&page=7',
        legacyArchiveThreadUrl($thread)
    );
});

/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/

test('legacy post URLs 301 to the archive page containing the post', function (string $template, string $prefix) {
    $thread = legacyThreadWithPosts(30);
    $post = legacyPostAt($thread, 25);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $post->postid]),
        legacyArchiveThreadUrl($thread, 2)
    );
})->with('legacy post forms')->with('legacy any prefix');

test('legacy post URL for the first post redirects to the bare archive thread', function (string $template) {
    $thread = legacyThreadWithPosts(30);
    $post = legacyPostAt($thread, 0);

    legacyAssertRedirectsTo(
        legacyUrl('forum/', $template, ['id' => $post->postid]),
        legacyArchiveThreadUrl($thread)
    );
})->with('legacy post forms');

test('legacy post URL resolves the archive page from chronological position', function (int $position, int $expectedPage) {
    $thread = legacyThreadWithPosts(30);
    $post = legacyPostAt($thread, $position);

    legacyAssertRedirectsTo(
        '/forum/showpost.php?p='.$post->postid,
        legacyArchiveThreadUrl($thread, $expectedPage)
    );
})->with([
    'first post' => [0, 1],
    'last post of page 1' => [24, 1],
    'first post of page 2' => [25, 2],
    'last post of thread' => [29, 2],
]);

test('legacy post URL orders posts sharing a dateline by post id', function () {
    $thread = legacyThreadWithPosts(30, sameDateline: true);
    $ordered = VbPost::query()->where('threadid', $thread->threadid)->orderBy('postid')->get();

    legacyAssertRedirectsTo(
        '/forum/showpost.php?p='.$ordered[24]->postid,
        legacyArchiveThreadUrl($thread)
    );
    legacyAssertRedirectsTo(
        '/forum/showpost.php?p='.$ordered[25]->postid,
        legacyArchiveThreadUrl($thread, 2)
    );
});

test('legacy post URL position ignores posts from other threads', function () {
    legacyThreadWithPosts(40);
    $thread = legacyThreadWithPosts(5);
    $post = legacyPostAt($thread, 4);

    legacyAssertRedirectsTo(
        '/forum/showpost.php?p='.$post->postid,
        legacyArchiveThreadUrl($thread)
    );
});

/*
|--------------------------------------------------------------------------
| Forums
|--------------------------------------------------------------------------
*/

test('legacy forum query-string URLs 301 to page 1 of the archive forum', function (string $template, string $prefix) {
    $forum = legacyForum(6);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $forum->forumid]),
        legacyArchiveForumUrl($forum)
    );
})->with('legacy forum query forms')->with('legacy any prefix');

test('legacy forum path-info and lite archive URLs 301 to page 1 of the archive forum', function (string $template, string $prefix) {
    $forum = legacyForum(6);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $forum->forumid]),
        legacyArchiveForumUrl($forum)
    );
})->with('legacy forum path-info forms')->with('legacy real prefix');

/*
|--------------------------------------------------------------------------
| Members
|--------------------------------------------------------------------------
*/

test('legacy member query-string URLs 301 to the mapped profile', function (string $template, string $prefix) {
    $vbUser = VbUser::factory()->create(['username' => 'buckeyefan']);
    $user = User::factory()->create(['username' => 'buckeyefan']);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, $template, ['id' => $vbUser->userid]),
        route('profile.show', ['user' => $user->username])
    );
})->with('legacy member query forms')->with('legacy any prefix');

test('legacy member path-info URLs 301 to the mapped profile', function (string $prefix) {
    $vbUser = VbUser::factory()->create(['username' => 'buckeyefan']);
    $user = User::factory()->create(['username' => 'buckeyefan']);

    legacyAssertRedirectsTo(
        legacyUrl($prefix, 'member.php/{id}-some-name', ['id' => $vbUser->userid]),
        route('profile.show', ['user' => $user->username])
    );
})->with('legacy real prefix');

test('legacy member URL 404s when the vBulletin user has no matching site user', function () {
    $vbUser = VbUser::factory()->create(['username' => 'ghostposter']);
    User::factory()->create(['username' => 'someoneelse']);

    legacyAssertNotFound('/forum/member.php?u='.$vbUser->userid);
});

test('legacy member URL 404s for an unknown vBulletin user id', function () {
    legacyAssertNotFound('/forum/member.php?u=987654');
});

test('legacy member URL 404s when the mapped user is flagged as spam', function () {
    $vbUser = VbUser::factory()->create(['username' => 'spammer']);
    User::factory()->create(['username' => 'spammer', 'is_flagged_spam' => true]);

    legacyAssertNotFound('/forum/member.php?u='.$vbUser->userid);
});

test('legacy member URL 404s when the mapped user is soft deleted', function () {
    $vbUser = VbUser::factory()->create(['username' => 'departed']);
    User::factory()->create(['username' => 'departed'])->delete();

    legacyAssertNotFound('/forum/member.php?u='.$vbUser->userid);
});

/*
|--------------------------------------------------------------------------
| Index and empty queries
|--------------------------------------------------------------------------
*/

test('legacy showthread.php without a thread id redirects to the archive index', function (string $prefix) {
    legacyAssertRedirectsTo(legacyUrl($prefix, 'showthread.php'), route('archive.index'));
})->with('legacy any prefix');

test('legacy forumdisplay.php without a forum id redirects to the archive index', function (string $prefix) {
    legacyAssertRedirectsTo(legacyUrl($prefix, 'forumdisplay.php'), route('archive.index'));
})->with('legacy any prefix');

test('legacy lite archive index redirects to the archive index', function (string $prefix) {
    legacyAssertRedirectsTo(legacyUrl($prefix, 'archive/index.php'), route('archive.index'));
})->with('legacy any prefix');

test('legacy lite archive index with trailing slash redirects to the archive index', function (string $prefix) {
    legacyAssertRedirectsTo(legacyUrl($prefix, 'archive/index.php/'), route('archive.index'));
})->with('legacy real prefix');

/*
|--------------------------------------------------------------------------
| Not found
|--------------------------------------------------------------------------
*/

test('legacy URLs for unknown ids 404', function (string $template) {
    legacyAssertNotFound(legacyUrl('forum/', $template, ['id' => 987654]));
})->with([
    'unknown thread t=' => 'showthread.php?t={id}',
    'unknown thread bare id-slug' => 'showthread.php?{id}-some-slug',
    'unknown thread path-info' => 'showthread.php/{id}-some-slug',
    'unknown thread lite archive' => 'archive/index.php/t-{id}.html',
    'unknown post via showthread' => 'showthread.php?p={id}',
    'unknown post via showpost' => 'showpost.php?p={id}',
    'unknown forum f=' => 'forumdisplay.php?f={id}',
    'unknown forum path-info' => 'forumdisplay.php/{id}-some-slug',
    'unknown forum lite archive' => 'archive/index.php/f-{id}.html',
]);

test('legacy URLs without a usable id 404 instead of hitting the homepage', function (string $template, string $prefix) {
    legacyAssertNotFound(legacyUrl($prefix, $template));
})->with([
    'unrelated showthread parameter' => 'showthread.php?foo=bar',
    'non-numeric thread id' => 'showthread.php?t=abc',
    'zero thread id' => 'showthread.php?t=0',
    'non-numeric forum id' => 'forumdisplay.php?f=abc',
    'showpost without a post id' => 'showpost.php',
    'member without a user id' => 'member.php',
    'non-numeric member id' => 'member.php?u=abc',
])->with('legacy any prefix');

test('unrecognised lite archive paths 404', function () {
    legacyAssertNotFound('/archive/index.php/garbage.html');
});

/*
|--------------------------------------------------------------------------
| Visibility
|--------------------------------------------------------------------------
*/

test('hidden threads and posts inside them 404 for guests', function (string $kind) {
    [$url] = legacyScenario($kind, 6, hidden: true);

    legacyAssertNotFound($url);
})->with('legacy thread bound entity kinds');

test('hidden threads and posts inside them 404 for admins too', function (string $kind) {
    actingAs(User::factory()->create(['is_admin' => true]));
    [$url] = legacyScenario($kind, 6, hidden: true);

    legacyAssertNotFound($url);
})->with('legacy thread bound entity kinds');

test('entities in a non-public forum 404 for guests', function (string $kind) {
    [$url] = legacyScenario($kind, 99);

    legacyAssertNotFound($url);
})->with('legacy entity kinds');

test('entities in a non-public forum 404 for non-admin members', function (string $kind) {
    actingAs(User::factory()->create(['is_admin' => false]));
    [$url] = legacyScenario($kind, 99);

    legacyAssertNotFound($url);
})->with('legacy entity kinds');

test('entities in a non-public forum redirect for admins', function (string $kind) {
    actingAs(User::factory()->create(['is_admin' => true]));
    [$url, $expected] = legacyScenario($kind, 99);

    legacyAssertRedirectsTo($url, $expected);
})->with('legacy entity kinds');

test('entities in a public forum redirect for guests', function (string $kind) {
    [$url, $expected] = legacyScenario($kind, 6);

    legacyAssertRedirectsTo($url, $expected);
})->with('legacy entity kinds');

/*
|--------------------------------------------------------------------------
| Existing behaviour that must keep working
|--------------------------------------------------------------------------
*/

test('other legacy forum paths still 301 to the new forums section', function () {
    get('/forum/some-other-path')
        ->assertStatus(301)
        ->assertRedirect('/forums/some-other-path');
});

test('the legacy forum root still 301s to the new forums section', function () {
    get('/forum')
        ->assertStatus(301)
        ->assertRedirect('/forums');
});
