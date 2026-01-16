<?php

namespace App\Modules\OhioWordle\Services;

use Illuminate\Support\Facades\Cache;

class DictionaryService
{
    private const SOWPODS_DICTIONARY_PATH = 'resources/data/dictionary/sowpods.txt';
    private const OHIO_WORDS_PATH = 'resources/data/dictionary/ohio.txt';
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
     * Get all words from both dictionaries.
     */
    public function getAllWords(): array
    {
        return Cache::remember('dictionary_all_words', self::CACHE_TTL, function () {
            $sowpodsWords = $this->getSowpodsWords();
            $ohioWords = $this->getOhioWords();

            return array_unique(array_merge($sowpodsWords, $ohioWords));
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
     * Get Ohio-specific words.
     */
    public function getOhioWords(): array
    {
        return Cache::remember('dictionary_ohio', self::CACHE_TTL, function () {
            return $this->loadWordsFromFile(self::OHIO_WORDS_PATH);
        });
    }

    /**
     * Clear all dictionary caches.
     */
    public function clearCache(): void
    {
        Cache::forget('dictionary_ohio');
        Cache::forget('dictionary_all_words');
        Cache::forget('dictionary_sowpods');

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
}
