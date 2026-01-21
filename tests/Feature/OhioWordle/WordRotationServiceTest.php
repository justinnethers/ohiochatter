<?php

use App\Modules\OhioWordle\Services\WordRotationService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->testDir = sys_get_temp_dir().'/wordle-test-'.uniqid();
    mkdir($this->testDir.'/resources/data/dictionary', 0755, true);
});

afterEach(function () {
    // Clean up test directory
    if (is_dir($this->testDir)) {
        array_map('unlink', glob($this->testDir.'/resources/data/dictionary/*'));
        rmdir($this->testDir.'/resources/data/dictionary');
        rmdir($this->testDir.'/resources/data');
        rmdir($this->testDir.'/resources');
        rmdir($this->testDir);
    }
});

function createService($testDir): WordRotationService
{
    return new WordRotationService($testDir);
}

function putTestFile($testDir, $filename, $content): void
{
    file_put_contents($testDir.'/resources/data/dictionary/'.$filename, $content);
}

function getTestFile($testDir, $filename): string
{
    return file_get_contents($testDir.'/resources/data/dictionary/'.$filename);
}

function testFileExists($testDir, $filename): bool
{
    return file_exists($testDir.'/resources/data/dictionary/'.$filename);
}

describe('getAvailableWords', function () {
    it('returns words from ohio.csv', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city in Ohio\nDAYTON,place,Another city\nTOLEDO,place,Yet another city");

        $service = createService($this->testDir);
        $words = $service->getAvailableWords();

        expect($words)->toBeArray();
        expect($words)->toContain('AKRON');
        expect($words)->toContain('DAYTON');
        expect($words)->toContain('TOLEDO');
    });

    it('returns empty array when file does not exist', function () {
        $service = createService($this->testDir);
        $words = $service->getAvailableWords();

        expect($words)->toBeArray();
        expect($words)->toBeEmpty();
    });

    it('returns empty array when file is empty', function () {
        putTestFile($this->testDir, 'ohio.csv', '');

        $service = createService($this->testDir);
        $words = $service->getAvailableWords();

        expect($words)->toBeArray();
        expect($words)->toBeEmpty();
    });

    it('trims whitespace and converts to uppercase', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\n  akron  ,place,A city\n  dayton  ,place,Another city");

        $service = createService($this->testDir);
        $words = $service->getAvailableWords();

        expect($words)->toContain('AKRON');
        expect($words)->toContain('DAYTON');
    });

    it('filters out non-alphabetic entries', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\n123,place,Invalid\nDAYTON,place,Another city\n!@#,place,Invalid\nTEST123,place,Invalid");

        $service = createService($this->testDir);
        $words = $service->getAvailableWords();

        expect($words)->toHaveCount(2);
        expect($words)->toContain('AKRON');
        expect($words)->toContain('DAYTON');
    });
});

describe('getRandomWord', function () {
    it('returns a random word from available words', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city\nTOLEDO,place,Yet another city");

        $service = createService($this->testDir);
        $word = $service->getRandomWord();

        expect($word)->toBeIn(['AKRON', 'DAYTON', 'TOLEDO']);
    });

    it('returns null when no words available', function () {
        $service = createService($this->testDir);
        $word = $service->getRandomWord();

        expect($word)->toBeNull();
    });
});

describe('removeWordFromAvailable', function () {
    it('removes word from ohio.csv', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city\nTOLEDO,place,Yet another city");

        $service = createService($this->testDir);
        $service->removeWordFromAvailable('DAYTON');

        $content = getTestFile($this->testDir, 'ohio.csv');
        expect($content)->not->toContain('DAYTON');
        expect($content)->toContain('AKRON');
        expect($content)->toContain('TOLEDO');
    });

    it('is case insensitive', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city\nTOLEDO,place,Yet another city");

        $service = createService($this->testDir);
        $service->removeWordFromAvailable('dayton');

        $content = getTestFile($this->testDir, 'ohio.csv');
        expect($content)->not->toContain('DAYTON');
    });

    it('does nothing if word not found', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city");

        $service = createService($this->testDir);
        $service->removeWordFromAvailable('TOLEDO');

        $words = $service->getAvailableWords();
        expect($words)->toHaveCount(2);
    });

    it('handles empty file gracefully', function () {
        putTestFile($this->testDir, 'ohio.csv', '');

        $service = createService($this->testDir);
        $service->removeWordFromAvailable('AKRON');

        expect(testFileExists($this->testDir, 'ohio.csv'))->toBeTrue();
    });
});

describe('addToUsedWords', function () {
    it('appends word to used_words.txt with timestamp', function () {
        $service = createService($this->testDir);
        $service->addToUsedWords('AKRON');

        $content = getTestFile($this->testDir, 'used_words.txt');
        expect($content)->toContain('AKRON');
        expect($content)->toContain('# Used on');
    });

    it('creates used_words.txt if it does not exist', function () {
        $service = createService($this->testDir);

        expect(testFileExists($this->testDir, 'used_words.txt'))->toBeFalse();

        $service->addToUsedWords('AKRON');

        expect(testFileExists($this->testDir, 'used_words.txt'))->toBeTrue();
    });

    it('appends to existing used_words.txt', function () {
        putTestFile($this->testDir, 'used_words.txt', "TOLEDO # Used on 2025-01-01\n");

        $service = createService($this->testDir);
        $service->addToUsedWords('AKRON');

        $content = getTestFile($this->testDir, 'used_words.txt');
        expect($content)->toContain('TOLEDO');
        expect($content)->toContain('AKRON');
    });
});

describe('hasAvailableWords', function () {
    it('returns true when words are available', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city");

        $service = createService($this->testDir);

        expect($service->hasAvailableWords())->toBeTrue();
    });

    it('returns false when no words available', function () {
        $service = createService($this->testDir);

        expect($service->hasAvailableWords())->toBeFalse();
    });

    it('returns false when file is empty', function () {
        putTestFile($this->testDir, 'ohio.csv', '');

        $service = createService($this->testDir);

        expect($service->hasAvailableWords())->toBeFalse();
    });
});

describe('getAvailableWordCount', function () {
    it('returns count of available words', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city\nDAYTON,place,Another city\nTOLEDO,place,Yet another city");

        $service = createService($this->testDir);

        expect($service->getAvailableWordCount())->toBe(3);
    });

    it('returns 0 when no words available', function () {
        $service = createService($this->testDir);

        expect($service->getAvailableWordCount())->toBe(0);
    });
});

describe('clearDictionaryCache', function () {
    it('clears dictionary cache keys', function () {
        Cache::put('dictionary_ohio', ['AKRON']);
        Cache::put('dictionary_ohio_csv', ['AKRON']);
        Cache::put('dictionary_ohio_csv_full', [['word' => 'AKRON', 'category' => 'place', 'description' => 'A city']]);
        Cache::put('dictionary_all_words', ['AKRON', 'HELLO']);
        Cache::put('dictionary_sowpods', ['HELLO']);
        Cache::put('dictionary_words_5', ['AKRON', 'HELLO']);

        $service = createService($this->testDir);
        $service->clearDictionaryCache();

        expect(Cache::has('dictionary_ohio'))->toBeFalse();
        expect(Cache::has('dictionary_ohio_csv'))->toBeFalse();
        expect(Cache::has('dictionary_ohio_csv_full'))->toBeFalse();
        expect(Cache::has('dictionary_all_words'))->toBeFalse();
        expect(Cache::has('dictionary_sowpods'))->toBeFalse();
        expect(Cache::has('dictionary_words_5'))->toBeFalse();
    });

    it('clears cache for various word lengths', function () {
        for ($i = 3; $i <= 15; $i++) {
            Cache::put("dictionary_words_{$i}", ['TEST']);
        }

        $service = createService($this->testDir);
        $service->clearDictionaryCache();

        for ($i = 3; $i <= 15; $i++) {
            expect(Cache::has("dictionary_words_{$i}"))->toBeFalse();
        }
    });
});

describe('getWordMetadata', function () {
    it('returns metadata for a word', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city in Summit County\nDAYTON,person,A famous person");

        $service = createService($this->testDir);
        $metadata = $service->getWordMetadata('AKRON');

        expect($metadata)->toBeArray();
        expect($metadata['category'])->toBe('place');
        expect($metadata['description'])->toBe('A city in Summit County');
    });

    it('returns null for non-existent word', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city");

        $service = createService($this->testDir);
        $metadata = $service->getWordMetadata('TOLEDO');

        expect($metadata)->toBeNull();
    });

    it('is case insensitive', function () {
        putTestFile($this->testDir, 'ohio.csv', "word,category,description\nAKRON,place,A city in Ohio");

        $service = createService($this->testDir);
        $metadata = $service->getWordMetadata('akron');

        expect($metadata)->toBeArray();
        expect($metadata['category'])->toBe('place');
    });
});
