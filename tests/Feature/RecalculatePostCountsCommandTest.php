<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('recalculates post count from threads and replies', function () {
    $user = User::factory()->create(['post_count' => 0]);
    $forum = Forum::factory()->create();

    // Create 2 threads
    Thread::factory()->count(2)->create([
        'user_id' => $user->id,
        'forum_id' => $forum->id,
    ]);

    // Create 3 replies
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);
    Reply::factory()->count(3)->create([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
    ]);

    // Reset post_count to simulate broken state (use query builder to bypass Eloquent dirty checking)
    User::where('id', $user->id)->update(['post_count' => 0]);

    $this->artisan('users:recalculate-post-counts')
        ->assertSuccessful();

    $user->refresh();
    expect($user->post_count)->toBe(5); // 2 threads + 3 replies
});

it('handles users with no posts', function () {
    $user = User::factory()->create(['post_count' => 99]);

    $this->artisan('users:recalculate-post-counts')
        ->assertSuccessful();

    $user->refresh();
    expect($user->post_count)->toBe(0);
});

it('preserves posts_old legacy count', function () {
    $user = User::factory()->create([
        'post_count' => 0,
        'posts_old' => 100,
    ]);
    $forum = Forum::factory()->create();

    Thread::factory()->create([
        'user_id' => $user->id,
        'forum_id' => $forum->id,
    ]);

    // Reset post_count to simulate broken state (use query builder to bypass Eloquent dirty checking)
    User::where('id', $user->id)->update(['post_count' => 0]);

    $this->artisan('users:recalculate-post-counts')
        ->assertSuccessful();

    $user->refresh();
    expect($user->post_count)->toBe(1);
    expect($user->posts_old)->toBe(100);
    expect($user->posts_count)->toBe(101); // total via accessor
});

it('excludes soft-deleted threads and replies from count', function () {
    $user = User::factory()->create(['post_count' => 0]);
    $forum = Forum::factory()->create();

    // Create 3 threads, delete 1
    $threads = Thread::factory()->count(3)->create([
        'user_id' => $user->id,
        'forum_id' => $forum->id,
    ]);
    $threads[0]->delete();

    // Create 4 replies, delete 2
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);
    $replies = Reply::factory()->count(4)->create([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
    ]);
    $replies[0]->delete();
    $replies[1]->delete();

    // Reset post_count (use query builder to bypass Eloquent dirty checking)
    User::where('id', $user->id)->update(['post_count' => 0]);

    $this->artisan('users:recalculate-post-counts')
        ->assertSuccessful();

    $user->refresh();
    expect($user->post_count)->toBe(4); // 2 threads + 2 replies (after deletions)
});

it('can recalculate for a single user', function () {
    $user1 = User::factory()->create(['post_count' => 99]);
    $user2 = User::factory()->create(['post_count' => 99]);
    $forum = Forum::factory()->create();

    Thread::factory()->create([
        'user_id' => $user1->id,
        'forum_id' => $forum->id,
    ]);

    // Reset post_count (use query builder to bypass Eloquent dirty checking)
    User::where('id', $user1->id)->update(['post_count' => 0]);

    $this->artisan('users:recalculate-post-counts', ['--user' => $user1->id])
        ->assertSuccessful();

    $user1->refresh();
    $user2->refresh();

    expect($user1->post_count)->toBe(1);
    expect($user2->post_count)->toBe(99); // unchanged
});

it('supports dry-run mode without making changes', function () {
    $user = User::factory()->create(['post_count' => 0]);
    $forum = Forum::factory()->create();

    Thread::factory()->count(3)->create([
        'user_id' => $user->id,
        'forum_id' => $forum->id,
    ]);

    // Reset post_count (use query builder to bypass Eloquent dirty checking)
    User::where('id', $user->id)->update(['post_count' => 0]);

    $this->artisan('users:recalculate-post-counts', ['--dry-run' => true])
        ->assertSuccessful();

    $user->refresh();
    expect($user->post_count)->toBe(0); // unchanged due to dry-run
});
