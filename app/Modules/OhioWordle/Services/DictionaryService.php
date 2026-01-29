<?php

namespace App\Modules\OhioWordle\Services;

use Illuminate\Support\Facades\Cache;

class DictionaryService
{
    private const SOWPODS_DICTIONARY_PATH = 'resources/data/dictionary/sowpods.txt';
    private const OHIO_WORDS_CSV_PATH = 'resources/data/dictionary/ohio.csv';
    private const PROPER_NOUNS_PATH = 'resources/data/dictionary/proper_nouns.txt';
    private const CACHE_TTL = 60 * 60 * 24; // 24 hours

    /**
     * Check if a word is valid for the given length.
     */
    public function isValidWord(string $word, int $length): bool
    {
        if (empty($word) || $length <= 0) {
            return false;
        }

        $word = strtoupper(trim($word));

        if (strlen($word) !== $length) {
            return false;
        }

        $validWords = $this->getWordsOfLength($length);

        return in_array($word, $validWords, true);
    }

    /**
     * Get all valid words of a specific length.
     */
    public function getWordsOfLength(int $length): array
    {
        if ($length <= 0) {
            return [];
        }

        return Cache::remember("dictionary_words_{$length}", self::CACHE_TTL, function () use ($length) {
            $allWords = $this->getAllWords();

            return array_values(array_filter($allWords, fn ($word) => strlen($word) === $length));
        });
    }

    /**
     * Get all words from all dictionaries.
     */
    public function getAllWords(): array
    {
        return Cache::remember('dictionary_all_words', self::CACHE_TTL, function () {
            $sowpodsWords = $this->getSowpodsWords();
            $ohioWords = $this->getOhioWords();
            $properNouns = $this->getProperNouns();

            return array_unique(array_merge($sowpodsWords, $ohioWords, $properNouns));
        });
    }

    /**
     * Get SOWPODS dictionary words.
     */
    public function getSowpodsWords(): array
    {
        return Cache::remember('dictionary_sowpods', self::CACHE_TTL, function () {
            return $this->loadWordsFromFile(self::SOWPODS_DICTIONARY_PATH);
        });
    }

    /**
     * Get Ohio-specific words (just the word list for backward compatibility).
     */
    public function getOhioWords(): array
    {
        return Cache::remember('dictionary_ohio_csv', self::CACHE_TTL, function () {
            $csvData = $this->loadWordsFromCsv(self::OHIO_WORDS_CSV_PATH);

            return array_column($csvData, 'word');
        });
    }

    /**
     * Get proper nouns (names, places not in SOWPODS).
     */
    public function getProperNouns(): array
    {
        return Cache::remember('dictionary_proper_nouns', self::CACHE_TTL, function () {
            return $this->loadWordsFromFile(self::PROPER_NOUNS_PATH);
        });
    }

    /**
     * Get metadata for a specific Ohio word.
     */
    public function getOhioWordMetadata(string $word): ?array
    {
        $word = strtoupper(trim($word));
        $csvData = Cache::remember('dictionary_ohio_csv_full', self::CACHE_TTL, function () {
            return $this->loadWordsFromCsv(self::OHIO_WORDS_CSV_PATH);
        });

        foreach ($csvData as $entry) {
            if ($entry['word'] === $word) {
                return [
                    'category' => $entry['category'] ?? null,
                    'description' => $entry['description'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Clear all dictionary caches.
     */
    public function clearCache(): void
    {
        Cache::forget('dictionary_ohio');
        Cache::forget('dictionary_ohio_csv');
        Cache::forget('dictionary_ohio_csv_full');
        Cache::forget('dictionary_all_words');
        Cache::forget('dictionary_sowpods');
        Cache::forget('dictionary_proper_nouns');

        for ($length = 3; $length <= 15; $length++) {
            Cache::forget("dictionary_words_{$length}");
        }
    }

    /**
     * Load words from a file.
     */
    private function loadWordsFromFile(string $path): array
    {
        $fullPath = base_path($path);

        if (! file_exists($fullPath)) {
            return [];
        }

        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);

        $words = [];
        foreach ($lines as $line) {
            $word = strtoupper(trim($line));
            if (! empty($word) && ctype_alpha($word)) {
                $words[] = $word;
            }
        }

        return $words;
    }

    /**
     * Load words from a CSV file with metadata.
     *
     * @return array<int, array{word: string, category: string, description: string}>
     */
    private function loadWordsFromCsv(string $path): array
    {
        $fullPath = base_path($path);

        if (! file_exists($fullPath)) {
            return [];
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            return [];
        }

        $data = [];
        $header = fgetcsv($handle); // Skip header row

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $word = strtoupper(trim($row[0]));
                if (! empty($word) && ctype_alpha($word)) {
                    $data[] = [
                        'word' => $word,
                        'category' => trim($row[1]),
                        'description' => trim($row[2]),
                    ];
                }
            }
        }

        fclose($handle);

        return $data;
    }
}
