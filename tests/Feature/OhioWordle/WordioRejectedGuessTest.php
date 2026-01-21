<?php

use App\Models\User;
use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordioService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->wordleService = app(WordioService::class);
    $this->user = User::factory()->create();
    $this->word = WordleWord::factory()->today()->create(['word' => 'AKRON']);
    Cache::flush();
});

describe('rejected guess logging', function () {
    it('logs rejected guesses when word is not in dictionary', function () {
        $this->wordleService->processGuess($this->user, 'XXXXX');

        $rejectedGuess = WordioRejectedGuess::first();

        expect($rejectedGuess)->not->toBeNull();
        expect($rejectedGuess->guess)->toBe('XXXXX');
        expect($rejectedGuess->reason)->toBe(WordioRejectedGuess::REASON_NOT_IN_DICTIONARY);
        expect($rejectedGuess->word_id)->toBe($this->word->id);
        expect($rejectedGuess->user_id)->toBe($this->user->id);
    });

    it('logs rejected guesses for wrong length', function () {
        $this->wordleService->processGuess($this->user, 'OHIO');

        $rejectedGuess = WordioRejectedGuess::first();

        expect($rejectedGuess)->not->toBeNull();
        expect($rejectedGuess->guess)->toBe('OHIO');
        expect($rejectedGuess->reason)->toBe(WordioRejectedGuess::REASON_WRONG_LENGTH);
    });

    it('logs rejected guesses for empty input', function () {
        $this->wordleService->processGuess($this->user, '');

        $rejectedGuess = WordioRejectedGuess::first();

        expect($rejectedGuess)->not->toBeNull();
        expect($rejectedGuess->guess)->toBe('');
        expect($rejectedGuess->reason)->toBe(WordioRejectedGuess::REASON_EMPTY);
    });

    it('logs rejected guesses for guest users with session id', function () {
        $this->wordleService->processGuess(null, 'XXXXX');

        $rejectedGuess = WordioRejectedGuess::first();

        expect($rejectedGuess)->not->toBeNull();
        expect($rejectedGuess->user_id)->toBeNull();
        expect($rejectedGuess->session_id)->not->toBeNull();
    });

    it('does not log valid guesses as rejected', function () {
        $this->wordleService->processGuess($this->user, 'HOUSE');

        expect(WordioRejectedGuess::count())->toBe(0);
    });

    it('truncates very long guesses', function () {
        WordioRejectedGuess::log('ABCDEFGHIJKLMNOPQRSTUVWXYZ', WordioRejectedGuess::REASON_WRONG_LENGTH);

        $rejectedGuess = WordioRejectedGuess::first();

        expect(strlen($rejectedGuess->guess))->toBe(20);
    });
});

describe('WordioRejectedGuess model', function () {
    it('belongs to a word', function () {
        $rejectedGuess = WordioRejectedGuess::create([
            'word_id' => $this->word->id,
            'guess' => 'XXXXX',
            'reason' => WordioRejectedGuess::REASON_NOT_IN_DICTIONARY,
        ]);

        expect($rejectedGuess->word->id)->toBe($this->word->id);
    });

    it('belongs to a user', function () {
        $rejectedGuess = WordioRejectedGuess::create([
            'word_id' => $this->word->id,
            'user_id' => $this->user->id,
            'guess' => 'XXXXX',
            'reason' => WordioRejectedGuess::REASON_NOT_IN_DICTIONARY,
        ]);

        expect($rejectedGuess->user->id)->toBe($this->user->id);
    });
});
