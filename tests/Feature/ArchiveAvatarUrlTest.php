<?php

use App\Models\VbCustomAvatar;
use App\Models\VbForum;
use App\Models\VbThread;
use App\Models\VbUser;

beforeEach(function () {
    config(['app.avatar_base_url' => 'https://cdn.example.com']);
});

test('archive avatar url is built from the avatar base url and archive path', function () {
    expect(VbCustomAvatar::urlForFilename('avatar123_1.gif'))
        ->toBe('https://cdn.example.com/storage/avatars/archive/avatar123_1.gif');
});

test('archive avatar url tolerates a trailing slash on the base url', function () {
    config(['app.avatar_base_url' => 'https://cdn.example.com/']);

    expect(VbCustomAvatar::urlForFilename('avatar123_1.gif'))
        ->toBe('https://cdn.example.com/storage/avatars/archive/avatar123_1.gif');
});

test('archive avatar filename is url encoded', function () {
    expect(VbCustomAvatar::urlForFilename('my avatar.gif'))
        ->toBe('https://cdn.example.com/storage/avatars/archive/my%20avatar.gif');
});

test('archive avatar path is configurable', function (string $path) {
    config(['app.archive_avatar_path' => $path]);

    expect(VbCustomAvatar::urlForFilename('x.gif'))
        ->toBe('https://cdn.example.com/archive-avatars/x.gif');
})->with([
    'plain' => 'archive-avatars',
    'surrounding slashes' => '/archive-avatars/',
]);

test('archive avatar url defaults to the app url and storage path', function () {
    config(['app.avatar_base_url' => config('app.url')]);

    expect(VbCustomAvatar::urlForFilename('x.gif'))
        ->toBe(rtrim(config('app.url'), '/').'/storage/avatars/archive/x.gif');
});

test('archive avatar path config defaults to storage/avatars/archive', function () {
    expect(config('app.archive_avatar_path'))->toBe('storage/avatars/archive');
});

test('custom avatar exposes its url as an attribute', function () {
    $avatar = VbCustomAvatar::factory()->make(['filename' => 'x.gif']);

    expect($avatar->url)->toBe(VbCustomAvatar::urlForFilename('x.gif'));
});

test('post owner component renders the archive avatar from the configured base url', function () {
    $this->blade('<x-post.owner :owner="null" username="tester" archive-avatar-filename="my avatar.gif" :link-profile="false" />')
        ->assertSee('https://cdn.example.com/storage/avatars/archive/my%20avatar.gif', false)
        ->assertDontSee('src="/storage/avatars/archive/', false);
});

test('archive forum page renders thread creator avatars from the configured base url', function () {
    $this->withoutVite();

    $forum = VbForum::factory()->create(['forumid' => 6]);
    $vbUser = VbUser::factory()->create();
    VbCustomAvatar::factory()->create(['userid' => $vbUser->userid, 'filename' => 'avatar5_2.gif']);
    VbThread::factory()->create(['forumid' => 6, 'postuserid' => $vbUser->userid]);

    $this->get(route('archive.forum', $forum))
        ->assertOk()
        ->assertSee('https://cdn.example.com/storage/avatars/archive/avatar5_2.gif', false)
        ->assertDontSee('src="/storage/avatars/archive/', false);
});
