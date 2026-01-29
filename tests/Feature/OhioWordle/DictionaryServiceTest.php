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
        // 5-letter Ohio city names from ohio.csv
        expect($this->dictionaryService->isValidWord('AKRON', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('BEREA', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('DOVER', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('PARMA', 5))->toBeTrue();
    });

    it('accepts Ohio landmark and geographic terms', function () {
        // 5-letter Ohio-related terms
        expect($this->dictionaryService->isValidWord('BUCKS', 5))->toBeTrue();
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
    it('loads sowpods dictionary', function () {
        $sowpodsWords = $this->dictionaryService->getSowpodsWords();

        expect($sowpodsWords)->toBeArray();
        expect($sowpodsWords)->not->toBeEmpty();
    });

    it('loads ohio words', function () {
        $ohioWords = $this->dictionaryService->getOhioWords();

        expect($ohioWords)->toBeArray();
        expect($ohioWords)->not->toBeEmpty();
        expect($ohioWords)->toContain('AKRON');
        expect($ohioWords)->toContain('BENCH'); // 5-letter words only in CSV
    });
});

describe('SOWPODS dictionary validation', function () {
    it('accepts valid SOWPODS words', function () {
        // Common words that should be in SOWPODS
        expect($this->dictionaryService->isValidWord('ABOUT', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('ZEBRA', 5))->toBeTrue();
        expect($this->dictionaryService->isValidWord('QUERY', 5))->toBeTrue();
    });

    it('accepts obscure but valid SOWPODS words', function () {
        // Words that are valid in SOWPODS but might not be in a basic dictionary
        expect($this->dictionaryService->isValidWord('AALII', 5))->toBeTrue(); // A Hawaiian shrub
        expect($this->dictionaryService->isValidWord('ZAXES', 5))->toBeTrue(); // Plural of zax
    });

    it('includes sowpods in getAllWords', function () {
        $allWords = $this->dictionaryService->getAllWords();

        // SOWPODS words should be included
        expect($allWords)->toContain('AALII');
        expect($allWords)->toContain('ZAXES');
    });
});

describe('proper nouns dictionary', function () {
    it('loads proper nouns', function () {
        $properNouns = $this->dictionaryService->getProperNouns();

        expect($properNouns)->toBeArray();
    });

    it('includes proper nouns in getAllWords', function () {
        $allWords = $this->dictionaryService->getAllWords();
        $properNouns = $this->dictionaryService->getProperNouns();

        foreach (array_slice($properNouns, 0, 3) as $noun) {
            expect($allWords)->toContain($noun);
        }
    });

    it('caches proper nouns for performance', function () {
        $this->dictionaryService->getProperNouns();

        expect(Cache::has('dictionary_proper_nouns'))->toBeTrue();
    });

    it('clears proper nouns cache when clearCache is called', function () {
        $this->dictionaryService->getProperNouns();
        expect(Cache::has('dictionary_proper_nouns'))->toBeTrue();

        $this->dictionaryService->clearCache();
        expect(Cache::has('dictionary_proper_nouns'))->toBeFalse();
    });
});
