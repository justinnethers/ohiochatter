<?php

use App\Livewire\Reputation;
use App\Models\Forum;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Database\Factories\NegFactory;
use Database\Factories\RepFactory;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    config(['scout.driver' => null]);

    $this->owner = User::factory()->create();
    $this->forum = Forum::factory()->create();
    $this->thread = Thread::factory()->create(['user_id' => $this->owner->id, 'forum_id' => $this->forum->id]);
    $this->reply = Reply::factory()->for($this->owner, 'owner')->for($this->thread)->create();
    $this->missingUserId = User::max('id') + 1000;
});

it('renders a rep from a soft-deleted user', function () {
    $repper = User::factory()->create(['username' => 'ghostrepper']);
    RepFactory::new()->forReply($this->reply)->create(['user_id' => $repper->id]);
    $repper->delete();

    expect(User::withTrashed()->find($repper->id)->trashed())->toBeTrue();

    Livewire::test(Reputation::class, ['post' => $this->reply->fresh()])
        ->assertOk()
        ->assertSee('ghostrepper');
});

it('renders a neg from a soft-deleted user', function () {
    $negger = User::factory()->create(['username' => 'ghostnegger']);
    NegFactory::new()->forReply($this->reply)->create(['user_id' => $negger->id]);
    $negger->delete();

    Livewire::test(Reputation::class, ['post' => $this->reply->fresh()])
        ->assertOk()
        ->assertSee('ghostnegger');
});

it('renders a rep whose user no longer exists', function () {
    RepFactory::new()->forReply($this->reply)->create(['user_id' => $this->missingUserId]);

    Livewire::test(Reputation::class, ['post' => $this->reply->fresh()])
        ->assertOk()
        ->assertSee('Deleted user')
        ->assertSee('images/avatars/default.png');
});

it('renders a neg whose user no longer exists', function () {
    NegFactory::new()->forReply($this->reply)->create(['user_id' => $this->missingUserId]);

    Livewire::test(Reputation::class, ['post' => $this->reply->fresh()])
        ->assertOk()
        ->assertSee('Deleted user')
        ->assertSee('images/avatars/default.png');
});

it('renders the thread page with reps from deleted users', function () {
    $repper = User::factory()->create(['username' => 'ghostrepper']);
    RepFactory::new()->forThread($this->thread)->create(['user_id' => $repper->id]);
    $repper->delete();
    RepFactory::new()->forReply($this->reply)->create(['user_id' => $this->missingUserId]);

    get("/forums/{$this->forum->slug}/{$this->thread->slug}")
        ->assertOk()
        ->assertSee('ghostrepper')
        ->assertSee('Deleted user');
});

it('keeps the rep count when a rep author is soft-deleted', function () {
    $live = User::factory()->create();
    $gone = User::factory()->create();
    RepFactory::new()->forReply($this->reply)->create(['user_id' => $live->id]);
    RepFactory::new()->forReply($this->reply)->create(['user_id' => $gone->id]);
    $gone->delete();

    expect($this->reply->fresh()->reps)->toHaveCount(2);

    Livewire::test(Reputation::class, ['post' => $this->reply->fresh()])->assertOk();
});
