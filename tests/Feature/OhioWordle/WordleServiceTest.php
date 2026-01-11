<?php

use App\Models\User;
use App\Modules\OhioWordle\Models\WordleAnonymousProgress;
use App\Modules\OhioWordle\Models\WordleUserProgress;
use App\Modules\OhioWordle\Models\WordleUserStats;
use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordleService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->wordleService = app(WordleService::class);
    $this->user = User::factory()->create();
    $this->word = WordleWord::factory()->today()->create(['word' => 'AKRON']);
    Cache::flush();
});

describe('WordleService constants', function () {
    it('has max guesses of 6', function () {
        expect(WordleService::MAX_GUESSES)->toBe(6);
    });
});

describe('getTodaysWord', function () {
    it('returns todays word', function () {
        $word = $this->wordleService->getTodaysWord();

        expect($word->id)->toBe($this->word->id);
        expect($word->word)->toBe('AKRON');
    });

    it('caches the result', function () {
        $this->wordleService->getTodaysWord();

        expect(Cache::has('todays_wordle_word'))->toBeTrue();
    });

    it('returns null when no word for today', function () {
        $this->word->delete();
        Cache::flush();

        $word = $this->wordleService->getTodaysWord();

        expect($word)->toBeNull();
    });
});

describe('getUserProgress', function () {
    it('creates new progress for user', function () {
        $progress = $this->wordleService->getUserProgress($this->user);

        expect($progress->user_id)->toBe($this->user->id);
        expect($progress->word_id)->toBe($this->word->id);
    });

    it('returns existing progress', function () {
        WordleUserProgress::factory()
            ->for($this->user)
            ->for($this->word, 'word')
            ->create(['attempts' => 3]);

        $progress = $this->wordleService->getUserProgress($this->user);

        expect($progress->attempts)->toBe(3);
    });

    it('returns null when no word today', function () {
        $this->word->delete();
        Cache::flush();

        $progress = $this->wordleService->getUserProgress($this->user);

        expect($progress)->toBeNull();
    });
});

describe('getGuestProgress', function () {
    it('returns null when no progress exists', function () {
        $progress = $this->wordleService->getGuestProgress();

        expect($progress)->toBeNull();
    });

    it('returns null when no word today', function () {
        $this->word->delete();
        Cache::flush();

        $progress = $this->wordleService->getGuestProgress();

        expect($progress)->toBeNull();
    });
});

describe('processGuess - validation', function () {
    it('rejects empty guesses', function () {
        $result = $this->wordleService->processGuess($this->user, '');

        expect($result['error'])->toBe('Please enter a guess');
        expect($result['valid'])->toBeFalse();
    });

    it('rejects guesses with wrong length', function () {
        $result = $this->wordleService->processGuess($this->user, 'OHIO');

        expect($result['error'])->toContain('must be 5 letters');
        expect($result['valid'])->toBeFalse();
    });

    it('rejects invalid words not in dictionary', function () {
        $result = $this->wordleService->processGuess($this->user, 'XXXXX');

        expect($result['error'])->toContain('not a valid word');
        expect($result['valid'])->toBeFalse();
    });
});

describe('processGuess - correct answer', function () {
    it('marks game as won on correct guess', function () {
        $result = $this->wordleService->processGuess($this->user, 'AKRON');

        expect($result['gameWon'])->toBeTrue();
        expect($result['gameComplete'])->toBeTrue();
        expect($result['valid'])->toBeTrue();
    });

    it('updates user stats on win', function () {
        $this->wordleService->processGuess($this->user, 'AKRON');

        $stats = WordleUserStats::where('user_id', $this->user->id)->first();

        expect($stats->games_won)->toBe(1);
        expect($stats->current_streak)->toBe(1);
    });

    it('records guess and feedback in progress', function () {
        $this->wordleService->processGuess($this->user, 'AKRON');

        $progress = WordleUserProgress::where('user_id', $this->user->id)->first();

        expect($progress->guesses)->toContain('AKRON');
        expect($progress->feedback)->toHaveCount(1);
        expect($progress->feedback[0])->toBe(['correct', 'correct', 'correct', 'correct', 'correct']);
    });

    it('sets remaining guesses correctly on first correct guess', function () {
        $result = $this->wordleService->processGuess($this->user, 'AKRON');

        expect($result['remainingGuesses'])->toBe(5);
    });

    it('sets guesses_taken on progress', function () {
        $this->wordleService->processGuess($this->user, 'AKRON');

        $progress = WordleUserProgress::where('user_id', $this->user->id)->first();

        expect($progress->guesses_taken)->toBe(1);
        expect($progress->solved)->toBeTrue();
    });
});

describe('processGuess - incorrect answer', function () {
    it('decrements remaining guesses', function () {
        $result = $this->wordleService->processGuess($this->user, 'HOUSE');

        expect($result['remainingGuesses'])->toBe(5);
        expect($result['gameWon'])->toBeFalse();
        expect($result['gameComplete'])->toBeFalse();
    });

    it('returns feedback for incorrect guess', function () {
        $result = $this->wordleService->processGuess($this->user, 'AROSE');

        expect($result['feedback'])->toBeArray();
        expect($result['feedback'])->toHaveCount(5);
        expect($result['feedback'][0])->toBe('correct'); // A matches
    });

    it('accumulates previous guesses', function () {
        $this->wordleService->processGuess($this->user, 'HOUSE');
        $result = $this->wordleService->processGuess($this->user, 'MOUSE');

        expect($result['previousGuesses'])->toHaveCount(2);
        expect($result['previousGuesses'])->toContain('HOUSE');
        expect($result['previousGuesses'])->toContain('MOUSE');
    });

    it('completes game after 6 wrong guesses', function () {
        for ($i = 1; $i <= 5; $i++) {
            $this->wordleService->processGuess($this->user, 'HOUSE');
        }
        $result = $this->wordleService->processGuess($this->user, 'MOUSE');

        expect($result['gameComplete'])->toBeTrue();
        expect($result['gameWon'])->toBeFalse();
        expect($result['remainingGuesses'])->toBe(0);
    });

    it('resets streak on loss', function () {
        WordleUserStats::factory()->for($this->user)->withStreak(5)->create();

        for ($i = 1; $i <= 6; $i++) {
            $this->wordleService->processGuess($this->user, 'HOUSE');
        }

        $stats = WordleUserStats::where('user_id', $this->user->id)->first();
        expect($stats->current_streak)->toBe(0);
    });
});

describe('processGuess - winning on later attempts', function () {
    it('can win on second guess', function () {
        $this->wordleService->processGuess($this->user, 'HOUSE');
        $result = $this->wordleService->processGuess($this->user, 'AKRON');

        expect($result['gameWon'])->toBeTrue();
        expect($result['previousGuesses'])->toHaveCount(2);
    });

    it('can win on sixth guess', function () {
        for ($i = 1; $i <= 5; $i++) {
            $this->wordleService->processGuess($this->user, 'HOUSE');
        }
        $result = $this->wordleService->processGuess($this->user, 'AKRON');

        expect($result['gameWon'])->toBeTrue();
        expect($result['remainingGuesses'])->toBe(0);

        $progress = WordleUserProgress::where('user_id', $this->user->id)->first();
        expect($progress->guesses_taken)->toBe(6);
    });

    it('updates guess distribution correctly', function () {
        // Win on 3rd guess
        $this->wordleService->processGuess($this->user, 'HOUSE');
        $this->wordleService->processGuess($this->user, 'MOUSE');
        $this->wordleService->processGuess($this->user, 'AKRON');

        $stats = WordleUserStats::where('user_id', $this->user->id)->first();
        expect($stats->guess_distribution['3'])->toBe(1);
    });
});

describe('loadWordStats', function () {
    it('calculates total players correctly', function () {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            WordleUserProgress::factory()->for($user)->for($this->word, 'word')->create();
        }
        WordleAnonymousProgress::factory()->count(2)->for($this->word, 'word')->create();

        $stats = $this->wordleService->loadWordStats($this->word);

        expect($stats['totalPlayers'])->toBe(5);
    });

    it('calculates completion rate', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        WordleUserProgress::factory()->solved()->for($user1)->for($this->word, 'word')->create();
        WordleUserProgress::factory()->failed()->for($user2)->for($this->word, 'word')->create();

        $stats = $this->wordleService->loadWordStats($this->word);

        expect($stats['completionRate'])->toEqual(50);
    });

    it('calculates average guesses for solved puzzles', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        WordleUserProgress::factory()->solved(2)->for($user1)->for($this->word, 'word')->create();
        WordleUserProgress::factory()->solved(4)->for($user2)->for($this->word, 'word')->create();

        $stats = $this->wordleService->loadWordStats($this->word);

        expect($stats['averageGuesses'])->toBe(3.0);
    });

    it('caches word stats', function () {
        $this->wordleService->loadWordStats($this->word);

        expect(Cache::has("wordle_stats_{$this->word->id}"))->toBeTrue();
    });
});
