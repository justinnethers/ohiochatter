<?php

namespace Tests\Unit\Actions;

use App\Actions\Threads\MoveThreadAction;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\EngineManager;
use Tests\TestCase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $engineManager = Mockery::mock(EngineManager::class);
    $engine = Mockery::mock(\Laravel\Scout\Engines\Engine::class);
    $engineManager->shouldReceive('engine')->andReturn($engine);
    $engine->shouldReceive('update')->andReturn(null);
    $engine->shouldReceive('delete')->andReturn(null);
    app()->instance(EngineManager::class, $engineManager);
});

test('admin can move thread to different forum', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $originalForum = Forum::factory()->create();
    $newForum = Forum::factory()->create();
    $thread = Thread::factory()->for($originalForum)->create();

    $this->actingAs($admin);
    $action = app(MoveThreadAction::class);
    $result = $action->execute($thread, $newForum->id);

    expect($result->forum_id)->toBe($newForum->id);
    $this->assertDatabaseHas('threads', [
        'id' => $thread->id,
        'forum_id' => $newForum->id,
    ]);
});

test('non-admin cannot move thread', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $originalForum = Forum::factory()->create();
    $newForum = Forum::factory()->create();
    $thread = Thread::factory()->for($originalForum)->create();

    $this->actingAs($user);
    $action = app(MoveThreadAction::class);

    expect(fn() => $action->execute($thread, $newForum->id))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('guest cannot move thread', function () {
    $originalForum = Forum::factory()->create();
    $newForum = Forum::factory()->create();
    $thread = Thread::factory()->for($originalForum)->create();

    $action = app(MoveThreadAction::class);

    expect(fn() => $action->execute($thread, $newForum->id))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('cannot move thread to same forum', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->for($forum)->create();

    $this->actingAs($admin);
    $action = app(MoveThreadAction::class);

    expect(fn() => $action->execute($thread, $forum->id))
        ->toThrow(\InvalidArgumentException::class);
});

test('cannot move thread to non-existent forum', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $forum = Forum::factory()->create();
    $thread = Thread::factory()->for($forum)->create();

    $this->actingAs($admin);
    $action = app(MoveThreadAction::class);

    expect(fn() => $action->execute($thread, 99999))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

afterEach(function () {
    Mockery::close();
});
