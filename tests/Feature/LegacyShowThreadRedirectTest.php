<?php

use App\Models\VbForum;
use App\Models\VbThread;

beforeEach(function () {
    config(['scout.driver' => null]);
});

function legacyShowThreadFixture(): VbThread
{
    $forum = VbForum::factory()->create(['forumid' => 6]);

    return VbThread::factory()->create([
        'threadid' => 48553,
        'forumid' => $forum->forumid,
        'visible' => 1,
    ]);
}

test('legacy showthread.php URL with id only redirects to named archive thread route', function () {
    $thread = legacyShowThreadFixture();

    $response = $this->get('/forum/showthread.php?48553');

    $response->assertStatus(301);
    $response->assertRedirect(route('archive.thread', $thread));
});

test('legacy showthread.php URL with id followed by trailing equals redirects to named archive thread route', function () {
    $thread = legacyShowThreadFixture();

    $response = $this->get('/forum/showthread.php?48553=');

    $response->assertStatus(301);
    $response->assertRedirect(route('archive.thread', $thread));
});

test('legacy showthread.php URL with empty query string redirects to archive index', function () {
    $response = $this->get('/forum/showthread.php');

    $response->assertStatus(301);
    $response->assertRedirect('/archive');
});
