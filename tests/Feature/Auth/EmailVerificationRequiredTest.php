<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    config(['scout.driver' => null]);
});

test('unverified users cannot create threads', function () {
    $user = User::factory()->unverified()->create();
    $forum = Forum::factory()->create();

    actingAs($user)
        ->post('/threads', [
            'forum_id' => $forum->id,
            'title' => 'Test Thread',
            'body' => 'Test Body',
        ])
        ->assertRedirect('/verify-email');
});

test('unverified users cannot create replies', function () {
    $user = User::factory()->unverified()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    actingAs($user)
        ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
            'body' => 'Test Reply',
        ])
        ->assertRedirect('/verify-email');
});

test('verified users can create threads', function () {
    $user = User::factory()->create(); // verified by default
    $forum = Forum::factory()->create();

    actingAs($user)
        ->post('/threads', [
            'forum_id' => $forum->id,
            'title' => 'Test Thread',
            'body' => 'Test Body',
        ])
        ->assertRedirect();

    expect(Thread::where('title', 'Test Thread')->exists())->toBeTrue();
});

test('verified users can create replies', function () {
    $user = User::factory()->create(); // verified by default
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    actingAs($user)
        ->post("/forums/{$forum->slug}/{$thread->slug}/replies", [
            'body' => 'Test Reply',
        ])
        ->assertRedirect();

    expect($thread->replies()->where('body', 'Test Reply')->exists())->toBeTrue();
});

test('unverified users are redirected to verification page when accessing thread create form', function () {
    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->get('/threads/create')
        ->assertRedirect('/verify-email');
});

test('unverified users see verification prompt instead of reply form on thread page', function () {
    $user = User::factory()->unverified()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    actingAs($user)
        ->get("/forums/{$forum->slug}/{$thread->slug}")
        ->assertStatus(200)
        ->assertSee('Verify your email to post');
});

test('verified users see reply form on thread page', function () {
    $user = User::factory()->create(); // verified by default
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    actingAs($user)
        ->get("/forums/{$forum->slug}/{$thread->slug}")
        ->assertStatus(200)
        ->assertSee('Submit Post');
});
