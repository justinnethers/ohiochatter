<?php

use App\Models\Forum;
use App\Models\Reply;
use App\Models\User;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('guest is redirected from forums to threads', function () {
    $forum = Forum::factory()->create([
        'name' => 'Test Forum',
        'is_restricted' => false
    ]);

    $response = $this->get('/forums');

    $response->assertRedirect('/threads');
});

test('guest can view threads', function () {
    $forum = Forum::factory()->create([
        'name' => 'Test Forum',
        'is_restricted' => false
    ]);

    $thread = Thread::factory()->for($forum)->create();

    $response = $this->get('/threads');

    $response->assertOk()
        ->assertSee($thread->title);
});

test('guest cannot view restricted forum threads', function () {
    $restrictedForum = Forum::factory()->create([
        'is_restricted' => true,
        'name' => 'Restricted Area'
    ]);

    $thread = Thread::factory()
        ->for($restrictedForum, 'forum')
        ->create();

    $response = $this->get("forums/{$restrictedForum->slug}");

    $response->assertStatus(302)
        ->assertRedirect('login');
});

test('authenticated user can view restricted forum threads', function () {
    $user = User::factory()->create();
    $restrictedForum = Forum::factory()->create([
        'is_restricted' => true,
        'name' => 'Restricted Area'
    ]);

    $thread = Thread::factory()
        ->for($restrictedForum, 'forum')
        ->create();

    $response = $this->actingAs($user)->get("forums/{$restrictedForum->slug}");

    $response->assertOk()
        ->assertSee($restrictedForum->name);
});

test('threads are ordered by their latest reply on main page', function() {
    $forum = Forum::factory()->create();

    $oldThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3)
    ]);

    $mediumThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2)
    ]);

    $newThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay()
    ]);

    Reply::factory()->create([
        'thread_id' => $oldThread->id,
        'created_at' => now()
    ]);

    $response = get('/threads');
    $threads = $response->viewData('threads');

    expect($threads->pluck('id')->toArray())->sequence(
        $oldThread->id,
        $newThread->id,
        $mediumThread->id
    );
});

test('threads are ordered by their latest reply in forum view', function() {
    $forum = Forum::factory()->create();

    $oldThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3)
    ]);

    $mediumThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2)
    ]);

    $newThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay()
    ]);

    Reply::factory()->create([
        'thread_id' => $oldThread->id,
        'created_at' => now()
    ]);

    $response = get("/forums/{$forum->slug}");
    $threads = $response->viewData('threads');

    expect($threads->pluck('id')->toArray())->sequence(
        $oldThread->id,
        $newThread->id,
        $mediumThread->id
    );
});

test('adding a reply moves thread to top on main page', function() {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();

    $oldThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2)
    ]);

    $newThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay()
    ]);

    actingAs($user)
        ->post("/forums/{$forum->slug}/{$oldThread->slug}/replies", [
            'body' => 'Some reply'
        ]);

    $response = get('/threads');
    $threads = $response->viewData('threads');

    expect($threads->pluck('id')->toArray())->sequence(
        $oldThread->id,
        $newThread->id
    );
});

test('adding a reply moves thread to top in forum view', function() {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();

    $oldThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2)
    ]);

    $newThread = Thread::factory()->create([
        'forum_id' => $forum->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay()
    ]);

    actingAs($user)
        ->post("/forums/{$forum->slug}/{$oldThread->slug}/replies", [
            'body' => 'Some reply'
        ]);

    $response = get("/forums/{$forum->slug}");
    $threads = $response->viewData('threads');

    expect($threads->pluck('id')->toArray())->sequence(
        $oldThread->id,
        $newThread->id
    );
});

test('threads from other forums do not affect ordering', function() {
    $forum1 = Forum::factory()->create();
    $forum2 = Forum::factory()->create();

    $oldThreadForum1 = Thread::factory()->create([
        'forum_id' => $forum1->id,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2)
    ]);

    $threadForum2 = Thread::factory()->create([
        'forum_id' => $forum2->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()  // More recent
    ]);

    $response = get("/forums/{$forum1->slug}");
    $threads = $response->viewData('threads');

    expect($threads)->toHaveCount(1)
        ->and($threads->first()->id)->toBe($oldThreadForum1->id);
});
