<?php

use App\Modules\OhioWordle\Services\DictionaryService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->dictionaryService = app(DictionaryService::class);
    Cache::flush();
});

describe('isValidWord - English dictionary words', function () {
    it('accepts common English words', function () {
        expect($this->dictionaryService->isValidWord('HELLO', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('WORLD', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('HOUSE', 5))->toBeTrue();
    });

    it('accepts words of various lengths', function () {
        expect($this->dictionaryService->isValidWord('THAT', 4))->toBeTrue();
        expect($this->dictionaryService->isValidWord('HELLO', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('STREET', 6))->toBeTrue();
    });

    it('rejects words that do not match required length', function () {
        expect($this->dictionaryService->isValidWord('HELLO', 4))->toBeFalse();
        expect($this->dictionaryService->isValidWord('HELLO', 6))->toBeFalse();
    });
});

describe('isValidWord - Ohio specific words', function () {
    it('accepts Ohio city names', function () {
        expect($this->dictionaryService->isValidWord('AKRON', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('DAYTON', 6))->toBeTrue();
        expect($this->dictionaryService->isValidWord('TOLEDO', 6))->toBeTrue();
        expect($this->dictionaryService->isValidWord('CANTON', 6))->toBeTrue();
    });

    it('accepts Ohio landmark and geographic terms', function () {
        expect($this->dictionaryService->isValidWord('OHIO', 4))->toBeTrue();
        expect($this->dictionaryService->isValidWord('BUCKEYE', 7))->toBeTrue();
    });

    it('accepts famous Ohioan names', function () {
        expect($this->dictionaryService->isValidWord('GRANT', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('HAYES', 5))->toBeTrue();
    });
});

describe('isValidWord - rejection of invalid words', function () {
    it('rejects random letter combinations', function () {
        expect($this->dictionaryService->isValidWord('XXXXX', 5))->toBeFalse();
        expect($this->dictionaryService->isValidWord('ZZZZZ', 5))->toBeFalse();
        expect($this->dictionaryService->isValidWord('QWERT', 5))->toBeFalse();
    });

    it('rejects gibberish strings', function () {
        expect($this->dictionaryService->isValidWord('ASDFG', 5))->toBeFalse();
        expect($this->dictionaryService->isValidWord('HJKLP', 5))->toBeFalse();
    });

    it('rejects empty strings', function () {
        expect($this->dictionaryService->isValidWord('', 0))->toBeFalse();
        expect($this->dictionaryService->isValidWord('', 5))->toBeFalse();
    });
});

describe('isValidWord - case handling', function () {
    it('accepts lowercase words', function () {
        expect($this->dictionaryService->isValidWord('hello', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('akron', 5))->toBeTrue();
    });

    it('accepts mixed case words', function () {
        expect($this->dictionaryService->isValidWord('HeLLo', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('AkRoN', 5))->toBeTrue();
    });
});

describe('getWordsOfLength', function () {
    it('returns all valid words of specified length', function () {
        $words = $this->dictionaryService->getWordsOfLength(5);

        expect($words)->toBeArray();
        expect($words)->toContain('HELLO');
        expect($words)->toContain('AKRON');
    });

    it('returns empty array for invalid length', function () {
        $words = $this->dictionaryService->getWordsOfLength(0);

        expect($words)->toBeArray();
        expect($words)->toBeEmpty();
    });

    it('caches results for performance', function () {
        $this->dictionaryService->getWordsOfLength(5);

        expect(Cache::has('dictionary_words_5'))->toBeTrue();
    });
});

describe('dictionary loading', function () {
    it('loads english dictionary', function () {
        // This verifies the dictionary file exists and can be loaded
        $englishWords = $this->dictionaryService->getEnglishWords();

        expect($englishWords)->toBeArray();
        expect($englishWords)->not->toBeEmpty();
    });

    it('loads ohio words', function () {
        $ohioWords = $this->dictionaryService->getOhioWords();

        expect($ohioWords)->toBeArray();
        expect($ohioWords)->not->toBeEmpty();
        expect($ohioWords)->toContain('AKRON');
        expect($ohioWords)->toContain('OHIO');
    });
});
