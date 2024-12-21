<?php

use App\Models\User;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $this->user = User::factory()->create();
});

test('user can view their threads', function() {
    $otherUser = User::factory()->create();

    $thread = Thread::create(['subject' => 'Test Thread']);

    Participant::create([
        'thread_id' => $thread->id,
        'user_id' => $this->user->id,
        'last_read' => now()
    ]);

    actingAs($this->user)
        ->get(route('messages.index'))
        ->assertOk()
        ->assertSee('Test Thread');
});

test('user can create new thread', function() {
    $recipient = User::factory()->create();

    actingAs($this->user)
        ->post(route('messages.store'), [
            'subject' => 'New Thread',
            'message' => 'Hello!',
            'recipients' => [$recipient->id]
        ])
        ->assertRedirect()
        ->assertSessionHas('message');

    expect(Thread::count())->toBe(1);
    expect(Message::count())->toBe(1);

    $this->assertDatabaseHas('pm_threads', [
        'subject' => 'New Thread'
    ]);

    $this->assertDatabaseHas('pm_messages', [
        'body' => 'Hello!'
    ]);
});

test('user can reply to thread', function() {
    $thread = Thread::create(['subject' => 'Test Thread']);

    Participant::create([
        'thread_id' => $thread->id,
        'user_id' => $this->user->id,
        'last_read' => now()
    ]);

    actingAs($this->user)
        ->post(route('messages.add_message', $thread), [
            'body' => 'My reply'
        ])
        ->assertRedirect()
        ->assertSessionHas('message');

    $this->assertDatabaseHas('pm_messages', [
        'body' => 'My reply',
        'user_id' => $this->user->id,
        'thread_id' => $thread->id
    ]);
});

test('user cannot view thread they are not participant of', function() {
    $thread = Thread::create(['subject' => 'Private Thread']);

    actingAs($this->user)
        ->get(route('messages.show', $thread))
        ->assertForbidden();
});

test('user cannot reply to thread they are not participant of', function() {
    $thread = Thread::create(['subject' => 'Private Thread']);

    actingAs($this->user)
        ->post(route('messages.add_message', $thread), [
            'body' => 'My reply'
        ])
        ->assertForbidden();

    expect(Message::count())->toBe(0);
});

test('thread requires at least one recipient', function() {
    actingAs($this->user)
        ->post(route('messages.store'), [
            'subject' => 'New Thread',
            'message' => 'Hello!',
            'recipients' => []
        ])
        ->assertSessionHasErrors('recipients');

    expect(Thread::count())->toBe(0);
});
