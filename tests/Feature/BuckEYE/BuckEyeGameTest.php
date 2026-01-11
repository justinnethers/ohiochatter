<?php

use App\Models\User;
use App\Modules\BuckEYE\Livewire\BuckEyeGame;
use App\Modules\BuckEYE\Models\AnonymousGameProgress;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameProgress;
use App\Modules\BuckEYE\Models\UserGameStats;
use App\Modules\BuckEYE\Services\PuzzleService;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->puzzle = Puzzle::factory()->today()->create([
        'answer' => 'Test Answer',
        'word_count' => 2,
        'hint' => 'This is a hint',
    ]);
    Cache::flush();
});

describe('component rendering', function () {
    it('renders for authenticated user', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->assertStatus(200);
    });

    it('renders for guest user', function () {
        Livewire::test(BuckEyeGame::class)
            ->assertStatus(200);
    });

    it('shows error when no puzzle available', function () {
        $this->puzzle->delete();
        Cache::flush();

        Livewire::test(BuckEyeGame::class)
            ->assertSet('errorMessage', 'No puzzle available for today.');
    });

    it('initializes game state correctly', function () {
        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('gameState.remainingGuesses'))->toBe(5);
        expect($component->get('gameState.pixelationLevel'))->toBe(5);
        expect($component->get('gameState.gameComplete'))->toBeFalse();
        expect($component->get('gameState.previousGuesses'))->toBe([]);
    });

    it('loads puzzle', function () {
        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('puzzle.id'))->toBe($this->puzzle->id);
    });

    it('sets image url', function () {
        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('imageUrl'))->not->toBeEmpty();
    });
});

describe('guess submission - authenticated user', function () {
    it('processes correct guess', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertSet('gameState.gameWon', true)
            ->assertSet('gameState.gameComplete', true)
            ->assertDispatched('gameCompleted');
    });

    it('processes incorrect guess', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong Answer')
            ->call('submitGuess')
            ->assertSet('gameState.gameWon', false)
            ->assertSet('gameState.remainingGuesses', 4)
            ->assertSet('errorMessage', 'Not quite. Try again!');
    });

    it('clears input after submission', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Guess')
            ->call('submitGuess')
            ->assertSet('currentGuess', '')
            ->assertDispatched('clearCurrentGuess');
    });

    it('validates guess is required', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', '')
            ->call('submitGuess')
            ->assertHasErrors(['currentGuess' => 'required']);
    });

    it('prevents submission on completed game', function () {
        UserGameProgress::factory()
            ->for($this->user)
            ->for($this->puzzle)
            ->solved()
            ->create();
        Cache::flush();

        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'New Guess')
            ->call('submitGuess')
            ->assertSet('errorMessage', 'This game is already complete.');
    });

    it('decreases pixelation level on incorrect guess', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong Answer')
            ->call('submitGuess')
            ->assertSet('gameState.pixelationLevel', 4);
    });

    it('sets pixelation to 0 on correct guess', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertSet('gameState.pixelationLevel', 0);
    });
});

describe('guess submission - guest user', function () {
    it('processes correct guess for guest', function () {
        Livewire::test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertSet('gameState.gameWon', true)
            ->assertSet('gameState.gameComplete', true);
    });

    it('saves anonymous progress on guess', function () {
        Livewire::test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong Guess')
            ->call('submitGuess');

        $this->assertDatabaseHas('anonymous_game_progress', [
            'puzzle_id' => $this->puzzle->id,
        ]);
    });

    it('processes incorrect guess for guest', function () {
        Livewire::test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong Answer')
            ->call('submitGuess')
            ->assertSet('gameState.gameWon', false)
            ->assertSet('gameState.remainingGuesses', 4)
            ->assertSet('errorMessage', 'Not quite. Try again!');
    });

    it('completes game after 5 wrong guesses for guest', function () {
        $component = Livewire::test(BuckEyeGame::class);

        for ($i = 1; $i <= 5; $i++) {
            $component->set('currentGuess', "Wrong $i")->call('submitGuess');
        }

        $component->assertSet('gameState.gameComplete', true)
            ->assertSet('gameState.gameWon', false);
    });
});

describe('game state restoration', function () {
    it('restores state for returning authenticated user', function () {
        UserGameProgress::factory()
            ->for($this->user)
            ->for($this->puzzle)
            ->create([
                'attempts' => 2,
                'previous_guesses' => ['guess1', 'guess2'],
            ]);
        Cache::flush();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('gameState.remainingGuesses'))->toBe(3);
        expect($component->get('gameState.previousGuesses'))->toContain('guess1');
        expect($component->get('gameState.previousGuesses'))->toContain('guess2');
    });

    it('shows puzzle stats for completed game', function () {
        UserGameProgress::factory()
            ->for($this->user)
            ->for($this->puzzle)
            ->solved()
            ->create();
        Cache::flush();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('showPuzzleStats'))->toBeTrue();
    });

    it('sets pixelation to 0 for completed game', function () {
        UserGameProgress::factory()
            ->for($this->user)
            ->for($this->puzzle)
            ->solved()
            ->create();
        Cache::flush();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('gameState.pixelationLevel'))->toBe(0);
    });
});

describe('user stats integration', function () {
    it('loads user stats on mount', function () {
        UserGameStats::factory()->for($this->user)->withGames(10, 8)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class);

        expect($component->get('userStats.games_played'))->toBe(10);
    });

    it('updates stats after correct guess', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess');

        $stats = UserGameStats::where('user_id', $this->user->id)->first();
        expect($stats->games_won)->toBe(1);
    });

    it('dispatches gameCompleted event on win', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertDispatched('gameCompleted');
    });
});

describe('puzzle stats display', function () {
    it('shows puzzle stats when game completes', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertSet('showPuzzleStats', true);
    });

    it('loads puzzle stats data', function () {
        // Add some other players' progress
        $otherUser = User::factory()->create();
        UserGameProgress::factory()->solved(3)->for($this->puzzle)->for($otherUser)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess');

        expect($component->get('puzzleStats'))->not->toBeNull();
        expect($component->get('puzzleStats.totalPlayers'))->toBeGreaterThanOrEqual(1);
    });
});

describe('multiple guesses', function () {
    it('accumulates previous guesses', function () {
        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong 1')
            ->call('submitGuess')
            ->set('currentGuess', 'Wrong 2')
            ->call('submitGuess');

        expect($component->get('gameState.previousGuesses'))->toHaveCount(2);
        expect($component->get('gameState.remainingGuesses'))->toBe(3);
    });

    it('can win on later attempts', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeGame::class)
            ->set('currentGuess', 'Wrong 1')
            ->call('submitGuess')
            ->set('currentGuess', 'Wrong 2')
            ->call('submitGuess')
            ->set('currentGuess', 'Test Answer')
            ->call('submitGuess')
            ->assertSet('gameState.gameWon', true)
            ->assertSet('gameState.previousGuesses', ['Wrong 1', 'Wrong 2', 'Test Answer']);
    });
});
