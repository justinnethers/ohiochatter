<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('reply post count', function () {
    it('increments user post_count when a reply is created', function () {
        $user = User::factory()->create(['post_count' => 0]);
        $thread = Thread::factory()->create();

        Reply::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(1);
    });

    it('decrements user post_count when a reply is deleted', function () {
        $user = User::factory()->create(['post_count' => 5]);
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(6);

        $reply->delete();

        $user->refresh();
        expect($user->post_count)->toBe(5);
    });

    it('increments user post_count when a reply is restored', function () {
        $user = User::factory()->create(['post_count' => 5]);
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(6);

        $reply->delete();
        $user->refresh();
        expect($user->post_count)->toBe(5);

        $reply->restore();
        $user->refresh();
        expect($user->post_count)->toBe(6);
    });
});

describe('thread post count', function () {
    it('increments user post_count when a thread is created', function () {
        $user = User::factory()->create(['post_count' => 0]);
        $forum = Forum::factory()->create();

        Thread::factory()->create([
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(1);
    });

    it('decrements user post_count when a thread is deleted', function () {
        $user = User::factory()->create(['post_count' => 5]);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->create([
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(6);

        $thread->delete();

        $user->refresh();
        expect($user->post_count)->toBe(5);
    });

    it('increments user post_count when a thread is restored', function () {
        $user = User::factory()->create(['post_count' => 5]);
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->create([
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);

        $user->refresh();
        expect($user->post_count)->toBe(6);

        $thread->delete();
        $user->refresh();
        expect($user->post_count)->toBe(5);

        $thread->restore();
        $user->refresh();
        expect($user->post_count)->toBe(6);
    });
});

describe('posts_count accessor', function () {
    it('combines post_count and posts_old for total', function () {
        $user = User::factory()->create([
            'post_count' => 10,
            'posts_old' => 50,
        ]);

        expect($user->posts_count)->toBe(60);
    });
});
