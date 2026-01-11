<?php

namespace App\Modules\OhioWordle\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DictionaryService
{
    private const ENGLISH_DICTIONARY_PATH = 'dictionary/english.txt';
    private const OHIO_WORDS_PATH = 'dictionary/ohio.txt';
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
            $englishWords = $this->getEnglishWords();
            $ohioWords = $this->getOhioWords();

            return array_unique(array_merge($englishWords, $ohioWords));
        });
    }

    /**
     * Get English dictionary words.
     */
    public function getEnglishWords(): array
    {
        return Cache::remember('dictionary_english', self::CACHE_TTL, function () {
            return $this->loadWordsFromFile(self::ENGLISH_DICTIONARY_PATH);
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
     * Load words from a file in storage.
     */
    private function loadWordsFromFile(string $path): array
    {
        if (! Storage::exists($path)) {
            return [];
        }

        $content = Storage::get($path);
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
