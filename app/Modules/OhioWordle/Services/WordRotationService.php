<?php

namespace App\Modules\OhioWordle\Services;

use Illuminate\Support\Facades\Cache;

class WordRotationService
{
    private string $ohioWordsPath;
    private string $usedWordsPath;

    public function __construct(?string $basePath = null)
    {
        $basePath = $basePath ?? base_path();
        $this->ohioWordsPath = $basePath.'/resources/data/dictionary/ohio.txt';
        $this->usedWordsPath = $basePath.'/resources/data/dictionary/used_words.txt';
    }

    public function getAvailableWords(): array
    {
        $fullPath = $this->ohioWordsPath;

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

    public function getRandomWord(): ?string
    {
        $words = $this->getAvailableWords();

        if (empty($words)) {
            return null;
        }

        return $words[array_rand($words)];
    }

    public function removeWordFromAvailable(string $word): void
    {
        $fullPath = $this->ohioWordsPath;

        if (! file_exists($fullPath)) {
            return;
        }

        $word = strtoupper(trim($word));
        $words = $this->getAvailableWords();
        $words = array_filter($words, fn ($w) => $w !== $word);

        file_put_contents($fullPath, implode("\n", array_values($words)));
    }

    public function addToUsedWords(string $word): void
    {
        $fullPath = $this->usedWordsPath;
        $word = strtoupper(trim($word));
        $timestamp = now()->toIso8601String();
        $entry = "{$word} # Used on {$timestamp}\n";

        file_put_contents($fullPath, $entry, FILE_APPEND);
    }

    public function hasAvailableWords(): bool
    {
        return $this->getAvailableWordCount() > 0;
    }

    public function getAvailableWordCount(): int
    {
        return count($this->getAvailableWords());
    }

    public function clearDictionaryCache(): void
    {
        Cache::forget('dictionary_ohio');
        Cache::forget('dictionary_all_words');
        Cache::forget('dictionary_sowpods');

        for ($length = 3; $length <= 15; $length++) {
            Cache::forget("dictionary_words_{$length}");
        }
    }
}
