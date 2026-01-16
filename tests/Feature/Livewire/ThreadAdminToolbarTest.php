<?php

use App\Livewire\ThreadAdminToolbar;
use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->forum = Forum::factory()->create(['is_active' => true]);
    $this->thread = Thread::factory()->for($this->forum)->create();
});

test('toolbar renders for admin users', function () {
    Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->assertStatus(200)
        ->assertSee('Move to');
});

test('toolbar does not render content for non-admin users', function () {
    Livewire::actingAs($this->user)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->assertDontSee('Move to')
        ->assertDontSee('Delete');
});

test('toolbar does not render content for guests', function () {
    Livewire::test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->assertDontSee('Move to')
        ->assertDontSee('Delete');
});

test('forums dropdown excludes current forum', function () {
    $otherForum = Forum::factory()->create(['is_active' => true, 'name' => 'Other Forum']);

    $component = Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread]);

    // Should see other forum but not current forum in dropdown
    $component->assertSee('Other Forum');
    // The component should have forums collection that excludes current forum
    expect($component->get('forums')->pluck('id')->toArray())
        ->not->toContain($this->forum->id)
        ->toContain($otherForum->id);
});

test('can move thread via toolbar', function () {
    $newForum = Forum::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->set('selectedForumId', $newForum->id)
        ->call('moveThread')
        ->assertRedirect();

    $this->thread->refresh();
    expect($this->thread->forum_id)->toBe($newForum->id);
});

test('can delete thread via toolbar', function () {
    Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->call('deleteThread')
        ->assertRedirect();

    $this->assertSoftDeleted('threads', ['id' => $this->thread->id]);
});

test('can toggle lock via toolbar', function () {
    expect((bool) $this->thread->locked)->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->call('toggleLock');

    $this->thread->refresh();
    expect((bool) $this->thread->locked)->toBeTrue();
});

test('lock toggle changes button state', function () {
    $component = Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread]);

    expect($component->get('isLocked'))->toBeFalse();

    $component->call('toggleLock');

    expect($component->get('isLocked'))->toBeTrue();
});

test('move requires forum selection', function () {
    Livewire::actingAs($this->admin)
        ->test(ThreadAdminToolbar::class, ['thread' => $this->thread])
        ->call('moveThread')
        ->assertHasErrors(['selectedForumId']);
});
