<?php

use App\Actions\Threads\HandleThreadNavigation;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['scout.driver' => null]);
    config(['forum.replies_per_page' => 25]);
    $this->action = new HandleThreadNavigation();
});

test('returns null when newestpost parameter is not present', function () {
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);
    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug);

    $result = $this->action->execute($request, $forum, $thread);

    expect($result)->toBeNull();
});

test('navigates to correct page when first unread reply is first post on new page', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    // Create 25 replies (fills page 1)
    $oldReplies = Reply::factory()->count(25)->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now()->subHour(),
    ]);

    // User viewed thread after the first 25 replies
    DB::table('threads_users_views')->insert([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
        'last_view' => Carbon::now()->subMinutes(30),
    ]);

    // Create 1 new reply (reply #26, first on page 2)
    $newReply = Reply::factory()->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now(),
    ]);

    $this->actingAs($user);
    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug . '?newestpost=true');

    $result = $this->action->execute($request, $forum, $thread);

    // Reply #26 should be on page 2
    expect($result)->toContain('page=2');
    expect($result)->toContain('#reply-' . $newReply->id);
});

test('navigates to page 1 when first unread reply is on page 1', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    // Create 24 replies (page 1 not full)
    $oldReplies = Reply::factory()->count(24)->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now()->subHour(),
    ]);

    // User viewed thread after the first 24 replies
    DB::table('threads_users_views')->insert([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
        'last_view' => Carbon::now()->subMinutes(30),
    ]);

    // Create 1 new reply (reply #25, still on page 1)
    $newReply = Reply::factory()->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now(),
    ]);

    $this->actingAs($user);
    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug . '?newestpost=true');

    $result = $this->action->execute($request, $forum, $thread);

    // Reply #25 should be on page 1
    expect($result)->toContain('page=1');
    expect($result)->toContain('#reply-' . $newReply->id);
});

test('navigates to correct page when multiple unread replies span pages', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    // Create 25 replies (fills page 1)
    Reply::factory()->count(25)->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now()->subHour(),
    ]);

    // User viewed thread after first 25 replies
    DB::table('threads_users_views')->insert([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
        'last_view' => Carbon::now()->subMinutes(30),
    ]);

    // Create 25 more replies (replies 26-50, spans pages 2)
    $newReplies = Reply::factory()->count(25)->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now(),
    ]);

    $this->actingAs($user);
    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug . '?newestpost=true');

    $result = $this->action->execute($request, $forum, $thread);

    // First unread reply (#26) is on page 2
    expect($result)->toContain('page=2');
    expect($result)->toContain('#reply-' . $newReplies->first()->id);
});

test('navigates to page 1 when all replies are unread', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    // User viewed thread before any replies
    DB::table('threads_users_views')->insert([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
        'last_view' => Carbon::now()->subHour(),
    ]);

    // Create 25 new replies (all unread)
    $newReplies = Reply::factory()->count(25)->create([
        'thread_id' => $thread->id,
        'created_at' => Carbon::now(),
    ]);

    $this->actingAs($user);
    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug . '?newestpost=true');

    $result = $this->action->execute($request, $forum, $thread);

    // First unread reply (#1) is on page 1
    expect($result)->toContain('page=1');
    expect($result)->toContain('#reply-' . $newReplies->first()->id);
});

test('non-logged-in user navigates to last page', function () {
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    // Create 26 replies (page 2 has 1 reply)
    $replies = Reply::factory()->count(26)->create([
        'thread_id' => $thread->id,
    ]);

    $request = Request::create('/forums/' . $forum->slug . '/' . $thread->slug . '?newestpost=true');

    $result = $this->action->execute($request, $forum, $thread);

    // Last reply is on page 2
    expect($result)->toContain('page=2');
    expect($result)->toContain('#reply-' . $replies->last()->id);
});