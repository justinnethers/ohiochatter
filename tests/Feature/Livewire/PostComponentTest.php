<?php

use App\Livewire\PostComponent;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->thread = Thread::factory()->for($this->user, 'owner')->create();
    $this->reply = Reply::factory()->for($this->user, 'owner')->for($this->thread)->create();
});

it('renders successfully', function () {
    $this->actingAs($this->user);

    Livewire::test(PostComponent::class, ['post' => $this->reply, 'firstPostOnPage' => false])
        ->assertStatus(200);
});

it('allows owner to toggle edit mode on their post', function () {
    $this->actingAs($this->user);

    Livewire::test(PostComponent::class, ['post' => $this->reply, 'firstPostOnPage' => false])
        ->assertSet('editMode', false)
        ->call('toggleEditMode')
        ->assertSet('editMode', true)
        ->call('toggleEditMode')
        ->assertSet('editMode', false);
});

it('allows owner to save edited post content', function () {
    $this->actingAs($this->user);

    $newBody = '<p>Updated content for testing</p>';

    Livewire::test(PostComponent::class, ['post' => $this->reply, 'firstPostOnPage' => false])
        ->call('toggleEditMode')
        ->set('body', $newBody)
        ->call('save')
        ->assertSet('editMode', false);

    expect($this->reply->fresh()->body)->toBe($newBody);
});

it('allows admin to edit any post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();
    $otherReply = Reply::factory()->for($otherUser, 'owner')->for($this->thread)->create();

    $this->actingAs($admin);

    Livewire::test(PostComponent::class, ['post' => $otherReply, 'firstPostOnPage' => false])
        ->assertSet('canEdit', true)
        ->call('toggleEditMode')
        ->assertSet('editMode', true);
});

it('does not allow non-owner to see edit button', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser);

    Livewire::test(PostComponent::class, ['post' => $this->reply, 'firstPostOnPage' => false])
        ->assertSet('canEdit', false);
});

it('does not allow guest to see edit button', function () {
    Livewire::test(PostComponent::class, ['post' => $this->reply, 'firstPostOnPage' => false])
        ->assertSet('canEdit', false);
});

it('works with Thread post type', function () {
    $this->actingAs($this->user);

    Livewire::test(PostComponent::class, ['post' => $this->thread, 'firstPostOnPage' => true])
        ->assertStatus(200)
        ->assertSet('canEdit', true)
        ->call('toggleEditMode')
        ->assertSet('editMode', true);
});
