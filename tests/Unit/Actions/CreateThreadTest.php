<?php

namespace Tests\Unit\Actions;

use App\Actions\Threads\CreateThread;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\EngineManager;
use Tests\TestCase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Mock the Scout Engine to prevent actual indexing
    $engineManager = Mockery::mock(EngineManager::class);
    $engine = Mockery::mock(\Laravel\Scout\Engines\Engine::class);
    $engineManager->shouldReceive('engine')->andReturn($engine);
    $engine->shouldReceive('update')->andReturn(null);
    $engine->shouldReceive('delete')->andReturn(null);
    app()->instance(EngineManager::class, $engineManager);
});

test('it creates a basic thread', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();

    $this->actingAs($user);

    $action = app(CreateThread::class);

    $thread = $action->execute([
        'title' => 'Test Thread',
        'body' => 'Test Body',
        'forum_id' => $forum->id,
        'has_poll' => false
    ]);

    expect($thread->title)->toBe('Test Thread')
        ->and($thread->body)->toBe('Test Body')
        ->and($thread->forum_id)->toBe($forum->id)
        ->and($thread->user_id)->toBe($user->id);
});

test('it creates a thread with poll', function () {
    $user = User::factory()->create();
    $forum = Forum::factory()->create();

    $this->actingAs($user);

    $action = app(CreateThread::class);

    $thread = $action->execute([
        'title' => 'Test Thread',
        'body' => 'Test Body',
        'forum_id' => $forum->id,
        'has_poll' => true,
        'poll_type' => 'single',
        'options' => ['Option 1', 'Option 2']
    ]);

    expect($thread->poll)->not->toBeNull()
        ->and($thread->poll->type)->toBe('single')
        ->and($thread->poll->pollOptions)->toHaveCount(2);
});

test('it throws exception when banned user tries to create thread', function () {
    $user = User::factory()->create(['is_banned' => true]);
    $forum = Forum::factory()->create();

    $this->actingAs($user);

    $action = app(CreateThread::class);

    expect(fn() => $action->execute([
        'title' => 'Test Thread',
        'body' => 'Test Body',
        'forum_id' => $forum->id,
        'has_poll' => false
    ]))->toThrow(\Exception::class, 'User is banned from creating threads.');
});

afterEach(function () {
    Mockery::close();
});
