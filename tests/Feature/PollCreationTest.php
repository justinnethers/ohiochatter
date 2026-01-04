<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    config(['scout.driver' => null]);
    $this->user = User::factory()->create();
    $this->forum = Forum::factory()->create();
});

describe('poll creation', function () {
    it('creates thread with poll and options', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Test Thread with Poll',
            'body' => 'Test body content',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => ['Option A', 'Option B', 'Option C'],
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll.pollOptions')->first();

        expect($thread)->not->toBeNull();
        expect($thread->poll)->not->toBeNull();
        expect($thread->poll->type)->toBe('single');
        expect($thread->poll->pollOptions)->toHaveCount(3);
        expect($thread->poll->pollOptions[0]->label)->toBe('Option A');
        expect($thread->poll->pollOptions[1]->label)->toBe('Option B');
        expect($thread->poll->pollOptions[2]->label)->toBe('Option C');
    });

    it('creates multiple choice poll', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Multiple Choice Poll Thread',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'multiple',
            'options' => ['Choice 1', 'Choice 2'],
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll')->first();
        expect($thread->poll->type)->toBe('multiple');
    });

    it('creates poll with end date when provided', function () {
        $endDate = now()->addDays(7)->format('Y-m-d\TH:i');

        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll with End Date',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => ['Yes', 'No'],
            'poll_ends_at' => $endDate,
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll')->first();
        expect($thread->poll->ends_at)->not->toBeNull();
        expect($thread->poll->ends_at->format('Y-m-d'))->toBe(now()->addDays(7)->format('Y-m-d'));
    });

    it('creates poll without end date when not provided', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll without End Date',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => ['Option 1', 'Option 2'],
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll')->first();
        expect($thread->poll->ends_at)->toBeNull();
    });

    it('validates end date must be in future', function () {
        $pastDate = now()->subDay()->format('Y-m-d\TH:i');

        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll with Past End Date',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => ['Yes', 'No'],
            'poll_ends_at' => $pastDate,
        ];

        actingAs($this->user)
            ->post('/threads', $data)
            ->assertSessionHasErrors('poll_ends_at');
    });

    it('validates poll requires at least 2 options', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll with One Option',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => ['Only One'],
        ];

        actingAs($this->user)
            ->post('/threads', $data)
            ->assertSessionHasErrors('options');
    });

    it('validates poll options are required when has_poll is true', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll without Options',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'single',
            'options' => [],
        ];

        actingAs($this->user)
            ->post('/threads', $data)
            ->assertSessionHasErrors('options');
    });

    it('validates poll type is required when has_poll is true', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll without Type',
            'body' => 'Test body',
            'has_poll' => 1,
            'options' => ['Option 1', 'Option 2'],
        ];

        actingAs($this->user)
            ->post('/threads', $data)
            ->assertSessionHasErrors('poll_type');
    });

    it('allows creating thread without poll', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Thread without Poll',
            'body' => 'Test body',
            'has_poll' => 0,
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll')->first();
        expect($thread)->not->toBeNull();
        expect($thread->poll)->toBeNull();
    });

    it('ignores poll fields when has_poll is false', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Thread without Poll',
            'body' => 'Test body',
            'has_poll' => 0,
            'poll_type' => 'single',
            'options' => ['Option 1', 'Option 2'],
        ];

        actingAs($this->user)->post('/threads', $data);

        $thread = Thread::with('poll')->first();
        expect($thread->poll)->toBeNull();
    });

    it('validates poll type must be single or multiple', function () {
        $data = [
            'forum_id' => $this->forum->id,
            'title' => 'Poll with Invalid Type',
            'body' => 'Test body',
            'has_poll' => 1,
            'poll_type' => 'invalid',
            'options' => ['Option 1', 'Option 2'],
        ];

        actingAs($this->user)
            ->post('/threads', $data)
            ->assertSessionHasErrors('poll_type');
    });
});
