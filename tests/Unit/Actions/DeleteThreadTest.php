<?php

namespace Tests\Unit\Actions;

use App\Actions\Threads\DeleteThreadAction;
use App\Models\Forum;
use App\Models\Reply;
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

test('admin can delete thread', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $thread = Thread::factory()->create();

    $this->actingAs($admin);
    $action = app(DeleteThreadAction::class);
    $result = $action->execute($thread);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('threads', ['id' => $thread->id]);
});

test('non-admin cannot delete thread', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $thread = Thread::factory()->create();

    $this->actingAs($user);
    $action = app(DeleteThreadAction::class);

    expect(fn() => $action->execute($thread))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('guest cannot delete thread', function () {
    $thread = Thread::factory()->create();

    $action = app(DeleteThreadAction::class);

    expect(fn() => $action->execute($thread))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('deleting thread soft deletes it', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $thread = Thread::factory()->create();

    $this->actingAs($admin);
    $action = app(DeleteThreadAction::class);
    $action->execute($thread);

    // Thread should still exist in DB but be soft deleted
    $this->assertDatabaseHas('threads', ['id' => $thread->id]);
    $this->assertSoftDeleted('threads', ['id' => $thread->id]);

    // Verify it can be restored
    expect(Thread::withTrashed()->find($thread->id))->not->toBeNull();
});

test('deleting thread cascades to replies', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $thread = Thread::factory()->create();
    $replies = Reply::factory()->count(3)->for($thread)->create();

    $this->actingAs($admin);
    $action = app(DeleteThreadAction::class);
    $action->execute($thread);

    // Thread should be soft deleted
    $this->assertSoftDeleted('threads', ['id' => $thread->id]);

    // Replies should also be soft deleted (Thread model's boot method handles this)
    foreach ($replies as $reply) {
        $this->assertSoftDeleted('replies', ['id' => $reply->id]);
    }
});

afterEach(function () {
    Mockery::close();
});
