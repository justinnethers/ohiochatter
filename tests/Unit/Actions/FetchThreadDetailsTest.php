<?php

use App\Actions\Threads\FetchThreadDetails;
use App\Models\Thread;
use App\Models\User;
use App\Models\PollVote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['scout.driver' => null]);
    $this->action = new FetchThreadDetails();
});

test('it fetches thread details without poll', function () {
    $thread = Thread::factory()->create();

    $details = $this->action->execute($thread);

    expect($details)
        ->toHaveKeys(['forum', 'thread', 'replies', 'poll', 'hasVoted', 'voteCount'])
        ->poll->toBeFalse()
        ->voteCount->toBe(0);
});

test('it fetches thread details with poll', function () {
    $voter = User::factory()->create();
    $currentUser = User::factory()->create();

    $thread = Thread::factory()
        ->hasPoll([
            'type' => 'single'
        ])
        ->create();

    $option = $thread->poll->pollOptions()->create(['label' => 'Option 1']);

    PollVote::create([
        'user_id' => $voter->id,
        'poll_option_id' => $option->id
    ]);

    $this->actingAs($currentUser);

    // Force reload the thread with all relationships
    $thread = $thread->fresh(['poll.pollOptions.votes.user']);

    $details = $this->action->execute($thread);

    expect($details)
        ->poll->not->toBeFalse()
        ->hasVoted->toBeFalse()
        ->voteCount->toBe(1);
});

test('it detects when current user has voted', function () {
    $user = User::factory()->create();

    $thread = Thread::factory()
        ->hasPoll([
            'type' => 'single'
        ])
        ->create();

    $option = $thread->poll->pollOptions()->create(['label' => 'Option 1']);

    $this->actingAs($user);

    PollVote::create([
        'user_id' => $user->id,
        'poll_option_id' => $option->id
    ]);

    // Force reload the thread with all relationships
    $thread = $thread->fresh(['poll.pollOptions.votes.user']);

    $details = $this->action->execute($thread);

    expect($details)
        ->hasVoted->toBeTrue()
        ->voteCount->toBe(1);
});
