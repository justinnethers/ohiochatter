<?php

use App\Models\User;
use App\Modules\BuckEYE\Models\AnonymousGameProgress;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameProgress;
use App\Modules\BuckEYE\Models\UserGameStats;
use App\Modules\BuckEYE\Services\PuzzleService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->puzzleService = app(PuzzleService::class);
    $this->user = User::factory()->create();
    $this->puzzle = Puzzle::factory()->today()->create(['answer' => 'Test Answer']);
    Cache::flush();
});

describe('PuzzleService constants', function () {
    it('has max guesses of 5', function () {
        expect(PuzzleService::MAX_GUESSES)->toBe(5);
    });

    it('has 5 pixelation levels', function () {
        expect(PuzzleService::PIXELATION_LEVELS)->toBe(5);
    });
});

describe('getTodaysPuzzle', function () {
    it('returns todays puzzle', function () {
        $puzzle = $this->puzzleService->getTodaysPuzzle();

        expect($puzzle->id)->toBe($this->puzzle->id);
    });

    it('caches the result', function () {
        $this->puzzleService->getTodaysPuzzle();

        expect(Cache::has('todays_puzzle'))->toBeTrue();
    });

    it('returns null when no puzzle for today', function () {
        $this->puzzle->delete();
        Cache::flush();

        $puzzle = $this->puzzleService->getTodaysPuzzle();

        expect($puzzle)->toBeNull();
    });
});

describe('getUserGameProgress', function () {
    it('creates new progress for user', function () {
        $progress = $this->puzzleService->getUserGameProgress($this->user);

        expect($progress->user_id)->toBe($this->user->id);
        expect($progress->puzzle_id)->toBe($this->puzzle->id);
    });

    it('returns existing progress', function () {
        UserGameProgress::factory()
            ->for($this->user)
            ->for($this->puzzle)
            ->create(['attempts' => 3]);

        $progress = $this->puzzleService->getUserGameProgress($this->user);

        expect($progress->attempts)->toBe(3);
    });

    it('returns null when no puzzle today', function () {
        $this->puzzle->delete();
        Cache::flush();

        $progress = $this->puzzleService->getUserGameProgress($this->user);

        expect($progress)->toBeNull();
    });
});

describe('getGuestGameProgress', function () {
    it('returns null when no progress exists', function () {
        $progress = $this->puzzleService->getGuestGameProgress();

        expect($progress)->toBeNull();
    });

    it('returns null when no puzzle today', function () {
        $this->puzzle->delete();
        Cache::flush();

        $progress = $this->puzzleService->getGuestGameProgress();

        expect($progress)->toBeNull();
    });
});

describe('processGuess - correct answer', function () {
    it('marks game as won on correct guess', function () {
        $result = $this->puzzleService->processGuess($this->user, 'Test Answer');

        expect($result['gameWon'])->toBeTrue();
        expect($result['gameComplete'])->toBeTrue();
        expect($result['pixelationLevel'])->toBe(0);
    });

    it('updates user stats on win', function () {
        $this->puzzleService->processGuess($this->user, 'Test Answer');

        $stats = UserGameStats::where('user_id', $this->user->id)->first();

        expect($stats->games_won)->toBe(1);
        expect($stats->current_streak)->toBe(1);
    });

    it('records guess in previous guesses', function () {
        $result = $this->puzzleService->processGuess($this->user, 'Test Answer');

        expect($result['previousGuesses'])->toContain('Test Answer');
    });

    it('sets remaining guesses to 4 on first correct guess', function () {
        $result = $this->puzzleService->processGuess($this->user, 'Test Answer');

        expect($result['remainingGuesses'])->toBe(4);
    });

    it('sets guesses_taken on progress', function () {
        $this->puzzleService->processGuess($this->user, 'Test Answer');

        $progress = UserGameProgress::where('user_id', $this->user->id)->first();

        expect($progress->guesses_taken)->toBe(1);
        expect($progress->solved)->toBeTrue();
    });
});

describe('processGuess - incorrect answer', function () {
    it('decrements remaining guesses', function () {
        $result = $this->puzzleService->processGuess($this->user, 'Wrong Answer');

        expect($result['remainingGuesses'])->toBe(4);
        expect($result['gameWon'])->toBeFalse();
        expect($result['gameComplete'])->toBeFalse();
    });

    it('decreases pixelation level', function () {
        $result = $this->puzzleService->processGuess($this->user, 'Wrong Answer');

        expect($result['pixelationLevel'])->toBe(4);
    });

    it('accumulates previous guesses', function () {
        $this->puzzleService->processGuess($this->user, 'Guess 1');
        $result = $this->puzzleService->processGuess($this->user, 'Guess 2');

        expect($result['previousGuesses'])->toContain('Guess 1');
        expect($result['previousGuesses'])->toContain('Guess 2');
        expect($result['previousGuesses'])->toHaveCount(2);
    });

    it('completes game after 5 wrong guesses', function () {
        for ($i = 1; $i <= 4; $i++) {
            $this->puzzleService->processGuess($this->user, "Wrong $i");
        }
        $result = $this->puzzleService->processGuess($this->user, 'Wrong 5');

        expect($result['gameComplete'])->toBeTrue();
        expect($result['gameWon'])->toBeFalse();
        expect($result['remainingGuesses'])->toBe(0);
    });

    it('resets streak on loss', function () {
        UserGameStats::factory()->for($this->user)->withStreak(5)->create();

        for ($i = 1; $i <= 5; $i++) {
            $this->puzzleService->processGuess($this->user, "Wrong $i");
        }

        $stats = UserGameStats::where('user_id', $this->user->id)->first();
        expect($stats->current_streak)->toBe(0);
    });

    it('pixelation level decreases with each guess', function () {
        $result1 = $this->puzzleService->processGuess($this->user, 'Wrong 1');
        expect($result1['pixelationLevel'])->toBe(4);

        $result2 = $this->puzzleService->processGuess($this->user, 'Wrong 2');
        expect($result2['pixelationLevel'])->toBe(3);

        $result3 = $this->puzzleService->processGuess($this->user, 'Wrong 3');
        expect($result3['pixelationLevel'])->toBe(2);

        $result4 = $this->puzzleService->processGuess($this->user, 'Wrong 4');
        expect($result4['pixelationLevel'])->toBe(1);

        $result5 = $this->puzzleService->processGuess($this->user, 'Wrong 5');
        expect($result5['pixelationLevel'])->toBe(0);
    });

    it('does not update games_won on loss', function () {
        for ($i = 1; $i <= 5; $i++) {
            $this->puzzleService->processGuess($this->user, "Wrong $i");
        }

        $stats = UserGameStats::where('user_id', $this->user->id)->first();
        expect($stats->games_won)->toBe(0);
        expect($stats->games_played)->toBe(1);
    });
});

describe('processGuess - winning on later attempts', function () {
    it('can win on second guess', function () {
        $this->puzzleService->processGuess($this->user, 'Wrong 1');
        $result = $this->puzzleService->processGuess($this->user, 'Test Answer');

        expect($result['gameWon'])->toBeTrue();
        expect($result['previousGuesses'])->toHaveCount(2);
    });

    it('can win on fifth guess', function () {
        for ($i = 1; $i <= 4; $i++) {
            $this->puzzleService->processGuess($this->user, "Wrong $i");
        }
        $result = $this->puzzleService->processGuess($this->user, 'Test Answer');

        expect($result['gameWon'])->toBeTrue();
        expect($result['remainingGuesses'])->toBe(0);

        $progress = UserGameProgress::where('user_id', $this->user->id)->first();
        expect($progress->guesses_taken)->toBe(5);
    });
});

describe('loadPuzzleStats', function () {
    it('calculates total players correctly', function () {
        // Create users with id > 1 (exclude admin user)
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            UserGameProgress::factory()->for($user)->for($this->puzzle)->create();
        }
        AnonymousGameProgress::factory()->count(2)->for($this->puzzle)->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['totalPlayers'])->toBe(5);
    });

    it('excludes user id 1 from stats', function () {
        $adminUser = User::find(1) ?? User::factory()->create(['id' => 1]);
        UserGameProgress::factory()->for($adminUser)->for($this->puzzle)->create();

        $regularUser = User::factory()->create();
        UserGameProgress::factory()->for($regularUser)->for($this->puzzle)->create();

        Cache::flush();
        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['totalPlayers'])->toBe(1);
    });

    it('calculates completion rate', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        UserGameProgress::factory()->solved()->for($this->puzzle)->for($user1)->create();
        UserGameProgress::factory()->failed()->for($this->puzzle)->for($user2)->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['completionRate'])->toEqual(50);
    });

    it('calculates average guesses for solved puzzles', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        UserGameProgress::factory()->solved(2)->for($this->puzzle)->for($user1)->create();
        UserGameProgress::factory()->solved(4)->for($this->puzzle)->for($user2)->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['averageGuesses'])->toBe(3.0);
    });

    it('caches puzzle stats', function () {
        $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect(Cache::has("puzzle_stats_{$this->puzzle->id}"))->toBeTrue();
    });

    it('returns N/A for average when no solved puzzles', function () {
        $user = User::factory()->create();
        UserGameProgress::factory()->failed()->for($this->puzzle)->for($user)->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['averageGuesses'])->toBe('N/A');
    });

    it('calculates guess distribution', function () {
        $users = User::factory()->count(5)->create();
        UserGameProgress::factory()->solved(1)->for($this->puzzle)->for($users[0])->create();
        UserGameProgress::factory()->solved(2)->for($this->puzzle)->for($users[1])->create();
        UserGameProgress::factory()->solved(2)->for($this->puzzle)->for($users[2])->create();
        UserGameProgress::factory()->solved(3)->for($this->puzzle)->for($users[3])->create();
        UserGameProgress::factory()->solved(5)->for($this->puzzle)->for($users[4])->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['guessDistribution'][1])->toBe(1);
        expect($stats['guessDistribution'][2])->toBe(2);
        expect($stats['guessDistribution'][3])->toBe(1);
        expect($stats['guessDistribution'][4])->toBe(0);
        expect($stats['guessDistribution'][5])->toBe(1);
    });

    it('includes anonymous players in stats', function () {
        $user = User::factory()->create();
        UserGameProgress::factory()->solved(2)->for($this->puzzle)->for($user)->create();
        AnonymousGameProgress::factory()->solved(4)->for($this->puzzle)->create();

        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['totalPlayers'])->toBe(2);
        expect($stats['solvedCount'])->toBe(2);
        expect($stats['averageGuesses'])->toBe(3.0);
    });

    it('returns zero completion rate for no players', function () {
        $stats = $this->puzzleService->loadPuzzleStats($this->puzzle);

        expect($stats['totalPlayers'])->toBe(0);
        expect($stats['completionRate'])->toBe(0);
    });
});

describe('getImage', function () {
    it('returns storage url for puzzle image', function () {
        $url = $this->puzzleService->getImage($this->puzzle);

        expect($url)->toContain($this->puzzle->image_path);
    });
});
