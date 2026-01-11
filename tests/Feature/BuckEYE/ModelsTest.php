<?php

use App\Models\User;
use App\Modules\BuckEYE\Models\AnonymousGameProgress;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameProgress;
use App\Modules\BuckEYE\Models\UserGameStats;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Puzzle model', function () {
    it('can be created with factory', function () {
        $puzzle = Puzzle::factory()->create();

        expect($puzzle)->toBeInstanceOf(Puzzle::class);
        expect($puzzle->answer)->not->toBeEmpty();
    });

    it('casts publish_date to date', function () {
        $puzzle = Puzzle::factory()->create(['publish_date' => '2025-03-15']);

        expect($puzzle->publish_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('casts alternate_answers to array', function () {
        $puzzle = Puzzle::factory()->withAlternateAnswers(['alt1', 'alt2'])->create();

        expect($puzzle->alternate_answers)->toBeArray();
        expect($puzzle->alternate_answers)->toContain('alt1');
        expect($puzzle->alternate_answers)->toContain('alt2');
    });

    it('can get todays puzzle', function () {
        Puzzle::factory()->today()->create(['answer' => 'Today Answer']);
        Puzzle::factory()->create(['publish_date' => now()->subDay()]);

        $todaysPuzzle = Puzzle::getTodaysPuzzle();

        expect($todaysPuzzle)->not->toBeNull();
        expect($todaysPuzzle->answer)->toBe('Today Answer');
    });

    it('returns null when no puzzle for today', function () {
        Puzzle::factory()->create(['publish_date' => now()->subDay()]);

        expect(Puzzle::getTodaysPuzzle())->toBeNull();
    });

    it('has many userProgress', function () {
        $puzzle = Puzzle::factory()->create();
        UserGameProgress::factory()->count(3)->for($puzzle)->create();

        expect($puzzle->userProgress)->toHaveCount(3);
    });

    it('checks exact answer match case insensitively', function () {
        $puzzle = Puzzle::factory()->create(['answer' => 'Ohio Stadium']);

        expect($puzzle->exactAnswerMatch('ohio stadium'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('OHIO STADIUM'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('Ohio Stadium'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('Wrong Answer'))->toBeFalse();
    });

    it('checks alternate answers for exact match', function () {
        $puzzle = Puzzle::factory()
            ->withAlternateAnswers(['The Shoe', 'Horseshoe'])
            ->create(['answer' => 'Ohio Stadium']);

        expect($puzzle->exactAnswerMatch('The Shoe'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('Horseshoe'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('the shoe'))->toBeTrue();
        expect($puzzle->exactAnswerMatch('Something Else'))->toBeFalse();
    });

    it('trims whitespace when checking exact match', function () {
        $puzzle = Puzzle::factory()->create(['answer' => 'Ohio Stadium']);

        expect($puzzle->exactAnswerMatch('  Ohio Stadium  '))->toBeTrue();
        expect($puzzle->exactAnswerMatch('Ohio Stadium '))->toBeTrue();
    });
});

describe('UserGameProgress model', function () {
    it('can be created with factory', function () {
        $progress = UserGameProgress::factory()->create();

        expect($progress)->toBeInstanceOf(UserGameProgress::class);
    });

    it('belongs to user', function () {
        $progress = UserGameProgress::factory()->for($this->user)->create();

        expect($progress->user->id)->toBe($this->user->id);
    });

    it('belongs to puzzle', function () {
        $puzzle = Puzzle::factory()->create();
        $progress = UserGameProgress::factory()->for($puzzle)->create();

        expect($progress->puzzle->id)->toBe($puzzle->id);
    });

    it('casts solved to boolean', function () {
        $progress = UserGameProgress::factory()->create(['solved' => 1]);

        expect($progress->solved)->toBeBool();
        expect($progress->solved)->toBeTrue();
    });

    it('casts previous_guesses to array', function () {
        $progress = UserGameProgress::factory()->create([
            'previous_guesses' => ['guess1', 'guess2'],
        ]);

        expect($progress->previous_guesses)->toBeArray();
        expect($progress->previous_guesses)->toContain('guess1');
    });

    it('casts completed_at to datetime', function () {
        $progress = UserGameProgress::factory()->solved()->create();

        expect($progress->completed_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('can use solved factory state', function () {
        $progress = UserGameProgress::factory()->solved(4)->create();

        expect($progress->solved)->toBeTrue();
        expect($progress->attempts)->toBe(4);
        expect($progress->guesses_taken)->toBe(4);
        expect($progress->previous_guesses)->toHaveCount(4);
    });

    it('can use failed factory state', function () {
        $progress = UserGameProgress::factory()->failed()->create();

        expect($progress->solved)->toBeFalse();
        expect($progress->attempts)->toBe(5);
        expect($progress->guesses_taken)->toBeNull();
        expect($progress->previous_guesses)->toHaveCount(5);
    });
});

describe('UserGameStats model', function () {
    it('can be created with factory', function () {
        $stats = UserGameStats::factory()->for($this->user)->create();

        expect($stats)->toBeInstanceOf(UserGameStats::class);
    });

    it('belongs to user', function () {
        $stats = UserGameStats::factory()->for($this->user)->create();

        expect($stats->user->id)->toBe($this->user->id);
    });

    it('can get or create for user', function () {
        $stats = UserGameStats::getOrCreateForUser($this->user->id);

        expect($stats->user_id)->toBe($this->user->id);
        expect($stats->games_played)->toBeIn([0, null]);
    });

    it('returns existing stats for user', function () {
        UserGameStats::factory()->for($this->user)->create(['games_played' => 10]);

        $stats = UserGameStats::getOrCreateForUser($this->user->id);

        expect($stats->games_played)->toBe(10);
    });

    it('updates stats after winning game', function () {
        $stats = UserGameStats::factory()->for($this->user)->create();

        $stats->updateAfterGame(true, 3);

        expect($stats->games_played)->toBe(1);
        expect($stats->games_won)->toBe(1);
        expect($stats->current_streak)->toBe(1);
        expect($stats->guess_distribution[3])->toBe(1);
    });

    it('updates stats after losing game', function () {
        $stats = UserGameStats::factory()->for($this->user)->withStreak(5)->create();

        $stats->updateAfterGame(false);

        expect($stats->games_played)->toBe(1);
        expect($stats->games_won)->toBe(0);
        expect($stats->current_streak)->toBe(0);
    });

    it('updates max streak when current exceeds it', function () {
        $stats = UserGameStats::factory()->for($this->user)->withStreak(3, 3)->create();

        $stats->updateAfterGame(true, 2);

        expect($stats->current_streak)->toBe(4);
        expect($stats->max_streak)->toBe(4);
    });

    it('does not update max streak when current is lower', function () {
        $stats = UserGameStats::factory()->for($this->user)->create([
            'current_streak' => 2,
            'max_streak' => 5,
        ]);

        $stats->updateAfterGame(true, 2);

        expect($stats->current_streak)->toBe(3);
        expect($stats->max_streak)->toBe(5);
    });

    it('accumulates guess distribution', function () {
        $stats = UserGameStats::factory()->for($this->user)->create([
            'guess_distribution' => [3 => 2],
        ]);

        $stats->updateAfterGame(true, 3);

        expect($stats->guess_distribution[3])->toBe(3);
    });

    it('updates last played date', function () {
        $stats = UserGameStats::factory()->for($this->user)->create();

        $stats->updateAfterGame(true, 3);

        expect($stats->last_played_date->toDateString())->toBe(now()->toDateString());
    });

    it('casts guess_distribution to array', function () {
        $stats = UserGameStats::factory()->for($this->user)->create([
            'guess_distribution' => [1 => 5, 2 => 10],
        ]);

        expect($stats->guess_distribution)->toBeArray();
    });
});

describe('AnonymousGameProgress model', function () {
    it('can be created with factory', function () {
        $progress = AnonymousGameProgress::factory()->create();

        expect($progress)->toBeInstanceOf(AnonymousGameProgress::class);
    });

    it('belongs to puzzle', function () {
        $puzzle = Puzzle::factory()->create();
        $progress = AnonymousGameProgress::factory()->for($puzzle)->create();

        expect($progress->puzzle->id)->toBe($puzzle->id);
    });

    it('casts solved to boolean', function () {
        $progress = AnonymousGameProgress::factory()->create(['solved' => 1]);

        expect($progress->solved)->toBeBool();
    });

    it('casts previous_guesses to array', function () {
        $progress = AnonymousGameProgress::factory()->create([
            'previous_guesses' => ['guess1', 'guess2'],
        ]);

        expect($progress->previous_guesses)->toBeArray();
    });

    it('casts completed_at to datetime', function () {
        $progress = AnonymousGameProgress::factory()->solved()->create();

        expect($progress->completed_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('can use solved factory state', function () {
        $progress = AnonymousGameProgress::factory()->solved(4)->create();

        expect($progress->solved)->toBeTrue();
        expect($progress->attempts)->toBe(4);
        expect($progress->guesses_taken)->toBe(4);
    });

    it('can use failed factory state', function () {
        $progress = AnonymousGameProgress::factory()->failed()->create();

        expect($progress->solved)->toBeFalse();
        expect($progress->attempts)->toBe(5);
    });

    it('stores session_id', function () {
        $progress = AnonymousGameProgress::factory()->create([
            'session_id' => 'test-session-123',
        ]);

        expect($progress->session_id)->toBe('test-session-123');
    });

    it('stores ip_address and user_agent', function () {
        $progress = AnonymousGameProgress::factory()->create([
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        expect($progress->ip_address)->toBe('192.168.1.1');
        expect($progress->user_agent)->toBe('Mozilla/5.0');
    });
});
