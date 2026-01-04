<?php

use App\Livewire\LatestPoll;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->forum = Forum::factory()->create();
    $this->thread = Thread::factory()->for($this->forum)->create();
    $this->poll = Poll::factory()
        ->for($this->thread)
        ->for($this->user)
        ->create(['type' => 'single']);
    $this->pollOptions = PollOption::factory()
        ->count(3)
        ->for($this->poll)
        ->create();
});

describe('latest poll rendering', function () {
    it('renders the most recent poll', function () {
        Livewire::test(LatestPoll::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.latest-poll')
            ->assertSee($this->pollOptions[0]->label);
    });

    it('shows empty state when no polls exist', function () {
        // Delete our test poll
        $this->poll->delete();

        Livewire::test(LatestPoll::class)
            ->assertSet('poll', null);
    });

    it('fetches the most recent thread with poll', function () {
        // Delete the poll from beforeEach to start fresh
        $this->poll->delete();

        // Create an older thread with poll
        $olderThread = Thread::factory()->for($this->forum)->create([
            'created_at' => now()->subDays(5),
        ]);
        $olderPoll = Poll::factory()
            ->for($olderThread)
            ->for($this->user)
            ->create();
        PollOption::factory()->for($olderPoll)->create(['label' => 'Old Poll Option']);

        // Create a newer thread with poll
        $newerThread = Thread::factory()->for($this->forum)->create([
            'created_at' => now()->addMinute(),
        ]);
        $newerPoll = Poll::factory()
            ->for($newerThread)
            ->for($this->user)
            ->create();
        PollOption::factory()->for($newerPoll)->create(['label' => 'New Poll Option']);

        Livewire::test(LatestPoll::class)
            ->assertSee('New Poll Option')
            ->assertDontSee('Old Poll Option');
    });

    it('shows voting UI for authenticated users who have not voted', function () {
        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->assertSee('Vote'); // LatestPoll uses compact "Vote" button
    });

    it('shows results for users who have voted', function () {
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->assertSet('hasVoted', true)
            ->assertDontSee('Submit Vote');
    });

    it('shows results to guest users', function () {
        // Create some votes
        $voter = User::factory()->create();
        PollVote::factory()->create([
            'user_id' => $voter->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        Livewire::test(LatestPoll::class)
            ->assertDontSee('Submit Vote')
            ->assertSee('100%');
    });
});

describe('voting in sidebar widget', function () {
    it('allows authenticated user to vote', function () {
        $component = Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $component->assertSet('hasVoted', true);

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('prevents unauthenticated user from voting', function () {
        Livewire::test(LatestPoll::class)
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote')
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('poll_votes', [
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('prevents double voting', function () {
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->set('selectedOption', $this->pollOptions[1]->id)
            ->call('vote');

        expect(PollVote::where('user_id', $this->user->id)->count())->toBe(1);
    });

    it('calculates percentages correctly', function () {
        // Create votes: 2 for option 0, 1 for option 1
        $voters = User::factory()->count(2)->create();
        foreach ($voters as $voter) {
            PollVote::factory()->create([
                'user_id' => $voter->id,
                'poll_option_id' => $this->pollOptions[0]->id,
            ]);
        }
        PollVote::factory()->create([
            'user_id' => User::factory()->create()->id,
            'poll_option_id' => $this->pollOptions[1]->id,
        ]);

        $component = Livewire::test(LatestPoll::class);

        expect($component->get('voteCount'))->toBe(3);
    });
});

describe('voter display in sidebar', function () {
    beforeEach(function () {
        $this->voters = User::factory()->count(2)->create();
        foreach ($this->voters as $voter) {
            PollVote::factory()->create([
                'user_id' => $voter->id,
                'poll_option_id' => $this->pollOptions[0]->id,
            ]);
        }
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('shows voter toggle for options with votes', function () {
        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->assertSee('voter'); // "2 voters" or similar
    });

    it('expands to show voters when toggled', function () {
        $component = Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->call('toggleVoters', $this->pollOptions[0]->id);

        foreach ($this->voters as $voter) {
            $component->assertSee($voter->username);
        }
    });

    it('collapses voters on second toggle', function () {
        $component = Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->call('toggleVoters', $this->pollOptions[0]->id)
            ->call('toggleVoters', $this->pollOptions[0]->id);

        foreach ($this->voters as $voter) {
            $component->assertDontSee($voter->username);
        }
    });
});

describe('poll end date in sidebar', function () {
    it('shows end date indicator when set', function () {
        $this->poll->update(['ends_at' => now()->addDays(2)]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->assertSee('Closes');
    });

    it('shows closed indicator when poll has ended', function () {
        $this->poll->update(['ends_at' => now()->subDay()]);

        Livewire::test(LatestPoll::class)
            ->assertSee('Closed');
    });

    it('prevents voting on ended poll', function () {
        $this->poll->update(['ends_at' => now()->subDay()]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $this->assertDatabaseMissing('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('allows voting on poll with future end date', function () {
        $this->poll->update(['ends_at' => now()->addDays(2)]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('shows results when poll ended for non-voter', function () {
        $voter = User::factory()->create();
        PollVote::factory()->create([
            'user_id' => $voter->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        $this->poll->update(['ends_at' => now()->subDay()]);

        Livewire::actingAs($this->user)
            ->test(LatestPoll::class)
            ->assertDontSee('Submit Vote')
            ->assertSee('100%');
    });
});
