<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Thread;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->thread = Thread::factory()->create();
    $this->poll = Poll::factory()
        ->for($this->thread)
        ->for($this->user)
        ->create();
});

describe('Poll model', function () {
    it('has many poll options', function () {
        $options = PollOption::factory()
            ->count(3)
            ->for($this->poll)
            ->create();

        expect($this->poll->pollOptions)->toHaveCount(3);
        expect($this->poll->pollOptions->first())->toBeInstanceOf(PollOption::class);
    });

    it('belongs to a user', function () {
        expect($this->poll->user)->toBeInstanceOf(User::class);
        expect($this->poll->user->id)->toBe($this->user->id);
    });

    it('belongs to a thread', function () {
        expect($this->poll->thread)->toBeInstanceOf(Thread::class);
        expect($this->poll->thread->id)->toBe($this->thread->id);
    });

    it('hasEnded returns false when ends_at is null', function () {
        $this->poll->update(['ends_at' => null]);

        expect($this->poll->fresh()->hasEnded())->toBeFalse();
    });

    it('hasEnded returns false when ends_at is in future', function () {
        $this->poll->update(['ends_at' => now()->addDays(2)]);

        expect($this->poll->fresh()->hasEnded())->toBeFalse();
    });

    it('hasEnded returns true when ends_at is in past', function () {
        $this->poll->update(['ends_at' => now()->subDay()]);

        expect($this->poll->fresh()->hasEnded())->toBeTrue();
    });

    it('hasEnded returns true when ends_at is exactly now', function () {
        Carbon::setTestNow(now());
        $this->poll->update(['ends_at' => now()->subSecond()]);

        expect($this->poll->fresh()->hasEnded())->toBeTrue();
    });

    it('casts ends_at to Carbon datetime', function () {
        $this->poll->update(['ends_at' => '2025-12-31 23:59:59']);

        $poll = $this->poll->fresh();

        expect($poll->ends_at)->toBeInstanceOf(Carbon::class);
        expect($poll->ends_at->year)->toBe(2025);
        expect($poll->ends_at->month)->toBe(12);
        expect($poll->ends_at->day)->toBe(31);
    });

    it('returns null for ends_at when not set', function () {
        expect($this->poll->ends_at)->toBeNull();
    });

    it('has correct type attribute', function () {
        $singlePoll = Poll::factory()->create(['type' => 'single']);
        $multiplePoll = Poll::factory()->create(['type' => 'multiple']);

        expect($singlePoll->type)->toBe('single');
        expect($multiplePoll->type)->toBe('multiple');
    });

    it('isActive returns opposite of hasEnded', function () {
        // Poll with no end date is active
        expect($this->poll->isActive())->toBeTrue();

        // Poll with future end date is active
        $this->poll->update(['ends_at' => now()->addDays(2)]);
        expect($this->poll->fresh()->isActive())->toBeTrue();

        // Poll with past end date is not active
        $this->poll->update(['ends_at' => now()->subDay()]);
        expect($this->poll->fresh()->isActive())->toBeFalse();
    });
});
