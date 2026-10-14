<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\VbForum;
use App\Models\VbThread;
use App\ValueObjects\SeoData;

use function Pest\Laravel\get;

beforeEach(function () {
    config(['scout.driver' => null]);
});

function threadWithReplies(int $replyCount): Thread
{
    $forum = Forum::factory()->create(['id' => 100]);
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);
    Reply::factory()->count($replyCount)->create(['thread_id' => $thread->id]);

    return $thread->fresh();
}

test('thread page 1 canonical is the bare thread url', function () {
    $thread = threadWithReplies(3);
    $threadUrl = route('thread.show', [$thread->forum, $thread]);

    get($thread->path())
        ->assertSuccessful()
        ->assertViewHas('seo', fn (SeoData $seo) => $seo->canonical === $threadUrl
            && ! str_contains($seo->title, 'Page'));
});

test('thread page 2 has a self-referencing canonical and a page number in the title', function () {
    $thread = threadWithReplies(30);
    $threadUrl = route('thread.show', [$thread->forum, $thread]);

    get($thread->path().'?page=2')
        ->assertSuccessful()
        ->assertViewHas('seo', fn (SeoData $seo) => $seo->canonical === $threadUrl.'?page=2'
            && $seo->ogUrl === $threadUrl.'?page=2'
            && str_ends_with($seo->title, ' - Page 2'));
});

test('thread structured data keeps pointing at the thread itself on later pages', function () {
    $thread = threadWithReplies(30);
    $threadUrl = route('thread.show', [$thread->forum, $thread]);

    get($thread->path().'?page=2')
        ->assertViewHas('seo', fn (SeoData $seo) => collect($seo->jsonLd)
            ->firstWhere('@type', 'DiscussionForumPosting')['url'] === $threadUrl);
});

test('archive thread page 2 has a self-referencing canonical', function () {
    $forum = VbForum::factory()->create(['forumid' => 6]);
    $thread = VbThread::factory()->create(['forumid' => $forum->forumid, 'visible' => 1]);
    $threadUrl = route('archive.thread', $thread);

    get("/archive/thread/{$thread->getRouteKey()}?page=2")
        ->assertSuccessful()
        ->assertViewHas('seo', fn (SeoData $seo) => $seo->canonical === $threadUrl.'?page=2'
            && str_ends_with($seo->title, ' - Page 2 - Forum Archive'));
});

test('archive thread page 1 canonical has no page parameter', function () {
    $forum = VbForum::factory()->create(['forumid' => 6]);
    $thread = VbThread::factory()->create(['forumid' => $forum->forumid, 'visible' => 1]);
    $threadUrl = route('archive.thread', $thread);

    get("/archive/thread/{$thread->getRouteKey()}")
        ->assertSuccessful()
        ->assertViewHas('seo', fn (SeoData $seo) => $seo->canonical === $threadUrl);
});

test('archive forum page 3 has a self-referencing canonical', function () {
    $forum = VbForum::factory()->create(['forumid' => 6]);
    VbThread::factory()->create(['forumid' => $forum->forumid, 'visible' => 1]);
    $forumUrl = route('archive.forum', $forum);

    get("/archive/forum/{$forum->getRouteKey()}?page=3")
        ->assertSuccessful()
        ->assertViewHas('seo', fn (SeoData $seo) => $seo->canonical === $forumUrl.'?page=3'
            && str_contains($seo->title, 'Page 3'));
});
