<?php

use App\Models\Forum;
use App\Models\Poll;
use App\Models\Thread;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    config(['scout.driver' => null]);
});

test('index displays paginated threads', function () {
    Thread::factory()->count(3)->create();

    get('/threads')
        ->assertStatus(200)
        ->assertViewHas('threads');
});

test('banned user cannot create thread', function () {
    $user = User::factory()->create(['is_banned' => true]);
    $forum = Forum::factory()->create();

    actingAs($user)
        ->post('/threads', [
            'forum_id' => $forum->id,
            'title' => 'Test Thread',
            'body' => 'Test Body'
        ])
        ->assertStatus(500)
        ->assertSee('User is banned from creating threads.');
});

test('thread can be created with poll', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();

    $data = [
        'forum_id' => $forum->id,
        'title' => 'Test Thread',
        'body' => 'Test Body',
        'has_poll' => 1,  // Change to integer 1
        'poll_type' => 'single',
        'options' => [
            'Option 1',
            'Option 2'
        ]
    ];

    $response = actingAs($user)->post('/threads', $data);

    $thread = Thread::with('poll.pollOptions')->first();

    expect($thread)
        ->not->toBeNull()
        ->title->toBe('Test Thread');

    expect($thread->poll)
        ->not->toBeNull();

    expect($thread->poll->pollOptions)
        ->toHaveCount(2);

    expect($thread->poll->pollOptions[0]->label)
        ->toBe('Option 1');
});

test('restricted forum requires login', function () {
    $forum = Forum::factory()->create(['is_restricted' => true]);
    $thread = Thread::factory()->create(['forum_id' => $forum->id]);

    get("/forums/{$forum->slug}/{$thread->slug}")
        ->assertRedirect('login');
});

test('user can view their own thread', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->create([
        'user_id' => $user->id,
        'forum_id' => $forum->id
    ]);

    actingAs($user)
        ->get("/forums/{$forum->slug}/{$thread->slug}")
        ->assertStatus(200)
        ->assertViewIs('threads.show');
});
