<?php

use App\Models\User;
use App\Modules\OhioWordle\Models\WordioValidGuess;
use App\Modules\OhioWordle\Services\DictionaryService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

describe('WordioValidGuess model', function () {
    it('can create a valid guess record', function () {
        $guess = WordioValidGuess::create([
            'word' => 'XYZZY',
        ]);

        expect($guess)->toBeInstanceOf(WordioValidGuess::class);
        expect($guess->word)->toBe('XYZZY');
    });

    it('normalizes words to uppercase', function () {
        $guess = WordioValidGuess::create([
            'word' => 'xyzzy',
        ]);

        expect($guess->word)->toBe('XYZZY');
    });

    it('trims whitespace from words', function () {
        $guess = WordioValidGuess::create([
            'word' => '  XYZZY  ',
        ]);

        expect($guess->word)->toBe('XYZZY');
    });

    it('enforces unique words', function () {
        WordioValidGuess::create(['word' => 'XYZZY']);

        expect(fn () => WordioValidGuess::create(['word' => 'XYZZY']))
            ->toThrow(Illuminate\Database\QueryException::class);
    });

    it('prevents duplicate words regardless of case', function () {
        WordioValidGuess::create(['word' => 'XYZZY']);

        expect(fn () => WordioValidGuess::create(['word' => 'xyzzy']))
            ->toThrow(Illuminate\Database\QueryException::class);
    });
});

describe('WordioValidGuess::approve static method', function () {
    it('creates a new valid guess record', function () {
        $user = User::factory()->create();

        $guess = WordioValidGuess::approve('XYZZY', $user->id);

        expect($guess)->toBeInstanceOf(WordioValidGuess::class);
        expect($guess->word)->toBe('XYZZY');
        expect($guess->approved_by)->toBe($user->id);
    });

    it('uses firstOrCreate to prevent duplicates', function () {
        $user = User::factory()->create();

        $guess1 = WordioValidGuess::approve('XYZZY', $user->id);
        $guess2 = WordioValidGuess::approve('XYZZY', $user->id);

        expect($guess1->id)->toBe($guess2->id);
        expect(WordioValidGuess::where('word', 'XYZZY')->count())->toBe(1);
    });

    it('normalizes the word to uppercase', function () {
        $user = User::factory()->create();

        $guess = WordioValidGuess::approve('xyzzy', $user->id);

        expect($guess->word)->toBe('XYZZY');
    });
});

describe('WordioValidGuess::getAllWords static method', function () {
    it('returns an array of all approved words', function () {
        WordioValidGuess::create(['word' => 'XYZZY']);
        WordioValidGuess::create(['word' => 'PLUGH']);

        $words = WordioValidGuess::getAllWords();

        expect($words)->toBeArray();
        expect($words)->toContain('XYZZY');
        expect($words)->toContain('PLUGH');
    });

    it('returns an empty array when no words exist', function () {
        $words = WordioValidGuess::getAllWords();

        expect($words)->toBeArray();
        expect($words)->toBeEmpty();
    });
});

describe('WordioValidGuess relationships', function () {
    it('belongs to user who approved it', function () {
        $user = User::factory()->create();
        $guess = WordioValidGuess::approve('XYZZY', $user->id);

        expect($guess->approvedBy)->toBeInstanceOf(User::class);
        expect($guess->approvedBy->id)->toBe($user->id);
    });

    it('can have null approved_by', function () {
        $guess = WordioValidGuess::create(['word' => 'XYZZY']);

        expect($guess->approved_by)->toBeNull();
        expect($guess->approvedBy)->toBeNull();
    });
});

describe('DictionaryService integration with approved guesses', function () {
    it('includes approved guesses in getAllWords', function () {
        $dictionaryService = app(DictionaryService::class);

        // Verify word is not in dictionary initially
        $initialWords = $dictionaryService->getAllWords();
        expect($initialWords)->not->toContain('XYZZY');

        // Approve the word
        WordioValidGuess::create(['word' => 'XYZZY']);
        $dictionaryService->clearCache();

        // Now it should be included
        $updatedWords = $dictionaryService->getAllWords();
        expect($updatedWords)->toContain('XYZZY');
    });

    it('validates approved guesses as valid words', function () {
        $dictionaryService = app(DictionaryService::class);

        // Should be invalid initially
        expect($dictionaryService->isValidWord('XYZZY', 5))->toBeFalse();

        // Approve the word
        WordioValidGuess::create(['word' => 'XYZZY']);
        $dictionaryService->clearCache();

        // Should now be valid
        expect($dictionaryService->isValidWord('XYZZY', 5))->toBeTrue();
    });

    it('caches approved guesses separately', function () {
        $dictionaryService = app(DictionaryService::class);
        WordioValidGuess::create(['word' => 'XYZZY']);

        $dictionaryService->getApprovedGuesses();

        expect(Cache::has('dictionary_approved_guesses'))->toBeTrue();
    });

    it('clears approved guesses cache when clearCache is called', function () {
        $dictionaryService = app(DictionaryService::class);
        WordioValidGuess::create(['word' => 'XYZZY']);

        $dictionaryService->getApprovedGuesses();
        expect(Cache::has('dictionary_approved_guesses'))->toBeTrue();

        $dictionaryService->clearCache();
        expect(Cache::has('dictionary_approved_guesses'))->toBeFalse();
    });
});
