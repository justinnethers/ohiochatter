<?php

use App\Livewire\DeletePostButton;
use App\Models\Reply;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function() {
    $this->user = User::factory()->create();
    $this->post = Reply::factory()->for($this->user, 'owner')->create();
});

it('renders successfully', function () {
    Livewire::test(DeletePostButton::class, ['post' => $this->post])
        ->assertStatus(200)
        ->assertViewIs('livewire.delete-post-button');
});

//it('deletes a post and dispatches event', function () {
//    Livewire::test(DeletePostButton::class, ['post' => $this->post])
//        ->call('delete')
//        ->assertDispatched('removed-post-' . $this->post->id);
//
//    $this->assertDatabaseMissing('replies', [
//        'id' => $this->post->id
//    ]);
//});

it('shows confirmation dialog before delete', function () {
    Livewire::test(DeletePostButton::class, ['post' => $this->post])
        ->assertSeeHtml('wire:confirm');
});

//it('can handle both Reply and Thread models', function () {
//    // Test with Reply
//    $reply = Reply::factory()->for($this->user, 'owner')->create();
//    Livewire::test(DeletePostButton::class, ['post' => $reply])
//        ->call('delete')
//        ->assertDispatched('removed-post-' . $reply->id);
//
//    // Test with Thread
//    $thread = Thread::factory()->for($this->user, 'owner')->create();
//    Livewire::test(DeletePostButton::class, ['post' => $thread])
//        ->call('delete')
//        ->assertDispatched('removed-post-' . $thread->id);
//});
