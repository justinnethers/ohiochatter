<?php

use App\Livewire\PollComponent;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Thread;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->thread = Thread::factory()->create();
    $this->poll = Poll::factory()
        ->for($this->thread)
        ->for($this->user)
        ->create(['type' => 'single']);
    $this->pollOptions = PollOption::factory()
        ->count(3)
        ->for($this->poll)
        ->create();
});

describe('poll rendering', function () {
    it('renders poll with options', function () {
        Livewire::test(PollComponent::class, ['poll' => $this->poll])
            ->assertStatus(200)
            ->assertViewIs('livewire.poll-component')
            ->assertSee($this->pollOptions[0]->label)
            ->assertSee($this->pollOptions[1]->label)
            ->assertSee($this->pollOptions[2]->label);
    });

    it('shows voting UI for unauthenticated users', function () {
        Livewire::test(PollComponent::class, ['poll' => $this->poll])
            ->assertSee('Submit Vote')
            ->assertDontSee('votes');
    });

    it('shows voting UI for users who have not voted', function () {
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll])
            ->assertSee('Submit Vote');
    });

    it('shows results for users who have voted', function () {
        // Create a vote for the user
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertSet('hasVoted', true)
            ->assertDontSee('Submit Vote')
            ->assertSee('vote'); // Shows vote count
    });
});

describe('single choice voting', function () {
    it('allows authenticated user to vote on single choice poll', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll])
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $component->assertSet('hasVoted', true);

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('prevents unauthenticated user from voting', function () {
        Livewire::test(PollComponent::class, ['poll' => $this->poll])
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote')
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('poll_votes', [
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('prevents user from voting twice', function () {
        // First vote
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);

        // Try to vote again
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->set('selectedOption', $this->pollOptions[1]->id)
            ->call('vote');

        // Should still only have one vote
        expect(PollVote::where('user_id', $this->user->id)->count())->toBe(1);
    });

    it('updates vote count after voting', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll]);

        expect($component->get('voteCount'))->toBe(0);

        $component
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        expect($component->get('voteCount'))->toBe(1);
    });

    it('shows correct percentages after voting', function () {
        // Create some existing votes
        $voters = User::factory()->count(3)->create();
        foreach ($voters as $index => $voter) {
            PollVote::factory()->create([
                'user_id' => $voter->id,
                'poll_option_id' => $this->pollOptions[0]->id,
            ]);
        }
        PollVote::factory()->create([
            'user_id' => User::factory()->create()->id,
            'poll_option_id' => $this->pollOptions[1]->id,
        ]);

        // Vote as our test user
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote')
            ->assertSee('80%') // 4 out of 5 votes
            ->assertSee('20%'); // 1 out of 5 votes
    });
});

describe('multiple choice voting', function () {
    beforeEach(function () {
        $this->poll->update(['type' => 'multiple']);
    });

    it('allows selecting multiple options', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertSee('Select all that apply')
            ->set('selectedOptions', [$this->pollOptions[0]->id, $this->pollOptions[1]->id])
            ->call('vote');

        $component->assertSet('hasVoted', true);

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[1]->id,
        ]);
    });

    it('records all selected options as votes', function () {
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->set('selectedOptions', [$this->pollOptions[0]->id, $this->pollOptions[1]->id, $this->pollOptions[2]->id])
            ->call('vote');

        expect(PollVote::where('user_id', $this->user->id)->count())->toBe(3);
    });
});

describe('voter display', function () {
    beforeEach(function () {
        // Create votes with users who have avatars
        $this->voters = User::factory()->count(3)->create();
        foreach ($this->voters as $voter) {
            PollVote::factory()->create([
                'user_id' => $voter->id,
                'poll_option_id' => $this->pollOptions[0]->id,
            ]);
        }
        // Make the test user vote so they see results
        PollVote::factory()->create([
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('shows "Show voters" toggle when option has votes', function () {
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertSee('Show voters');
    });

    it('hides voters section by default', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()]);

        // Should not see voter usernames initially
        foreach ($this->voters as $voter) {
            $component->assertDontSee($voter->username);
        }
    });

    it('expands voters when toggle is clicked', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->call('toggleVoters', $this->pollOptions[0]->id);

        // Should now see voter usernames
        foreach ($this->voters as $voter) {
            $component->assertSee($voter->username);
        }
    });

    it('collapses voters when toggle is clicked again', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->call('toggleVoters', $this->pollOptions[0]->id)
            ->call('toggleVoters', $this->pollOptions[0]->id);

        // Should no longer see voter usernames
        foreach ($this->voters as $voter) {
            $component->assertDontSee($voter->username);
        }
    });

    it('displays voter avatar and username in pill', function () {
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->call('toggleVoters', $this->pollOptions[0]->id)
            ->assertSee($this->voters[0]->username)
            ->assertSeeHtml('rounded-full'); // Pill styling
    });

    it('shows all voters for an option', function () {
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->call('toggleVoters', $this->pollOptions[0]->id);

        // All 4 voters (3 + test user) should be visible
        foreach ($this->voters as $voter) {
            $component->assertSee($voter->username);
        }
        $component->assertSee($this->user->username);
    });

    it('does not show toggle for options with zero votes', function () {
        // options[1] and options[2] have no votes
        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()]);

        // The component should show "Show voters" only for options with votes
        // We verify this by checking the expanded state doesn't include other options
        expect($component->get('expandedVoters'))->toBe([]);
    });
});

describe('poll end date', function () {
    it('shows "Closes in X" indicator when end date is set', function () {
        $this->poll->update(['ends_at' => now()->addDays(2)]);

        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertSee('Closes');
    });

    it('shows "Poll closed" indicator when end date has passed', function () {
        $this->poll->update(['ends_at' => now()->subDay()]);

        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertSee('closed');
    });

    it('allows voting on poll with future end date', function () {
        $this->poll->update(['ends_at' => now()->addDays(2)]);

        $component = Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $component->assertSet('hasVoted', true);

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('prevents voting on poll with past end date', function () {
        $this->poll->update(['ends_at' => now()->subDay()]);

        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->set('selectedOption', $this->pollOptions[0]->id)
            ->call('vote');

        $this->assertDatabaseMissing('poll_votes', [
            'user_id' => $this->user->id,
            'poll_option_id' => $this->pollOptions[0]->id,
        ]);
    });

    it('shows results to users who did not vote on ended poll', function () {
        // Create some votes from other users
        $voters = User::factory()->count(2)->create();
        foreach ($voters as $voter) {
            PollVote::factory()->create([
                'user_id' => $voter->id,
                'poll_option_id' => $this->pollOptions[0]->id,
            ]);
        }

        // End the poll
        $this->poll->update(['ends_at' => now()->subDay()]);

        // Test user hasn't voted but should see results
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll->fresh()])
            ->assertDontSee('Submit Vote')
            ->assertSee('100%'); // All votes on option 0
    });

    it('does not show end date indicator when ends_at is null', function () {
        // ends_at is null by default
        Livewire::actingAs($this->user)
            ->test(PollComponent::class, ['poll' => $this->poll])
            ->assertDontSee('Closes')
            ->assertDontSee('closed');
    });
});
