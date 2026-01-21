<?php

namespace App\Modules\OhioWordle\Services;

use Illuminate\Support\Facades\Cache;

class WordRotationService
{
    private string $ohioWordsCsvPath;
    private string $usedWordsPath;

    public function __construct(?string $basePath = null)
    {
        $basePath = $basePath ?? base_path();
        $this->ohioWordsCsvPath = $basePath.'/resources/data/dictionary/ohio.csv';
        $this->usedWordsPath = $basePath.'/resources/data/dictionary/used_words.txt';
    }

    public function getAvailableWords(): array
    {
        $csvData = $this->loadCsvData();

        return array_column($csvData, 'word');
    }

    /**
     * Get metadata for a specific word.
     */
    public function getWordMetadata(string $word): ?array
    {
        $word = strtoupper(trim($word));
        $csvData = $this->loadCsvData();

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
     * Load CSV data from file.
     *
     * @return array<int, array{word: string, category: string, description: string}>
     */
    private function loadCsvData(): array
    {
        if (! file_exists($this->ohioWordsCsvPath)) {
            return [];
        }

        $handle = fopen($this->ohioWordsCsvPath, 'r');
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
        if (! file_exists($this->ohioWordsCsvPath)) {
            return;
        }

        $word = strtoupper(trim($word));
        $csvData = $this->loadCsvData();
        $filteredData = array_filter($csvData, fn ($entry) => $entry['word'] !== $word);

        // Rewrite CSV file with remaining entries
        $handle = fopen($this->ohioWordsCsvPath, 'w');
        if ($handle === false) {
            return;
        }

        // Write header
        fputcsv($handle, ['word', 'category', 'description']);

        // Write data rows
        foreach ($filteredData as $entry) {
            fputcsv($handle, [$entry['word'], $entry['category'], $entry['description']]);
        }

        fclose($handle);
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
        Cache::forget('dictionary_ohio_csv');
        Cache::forget('dictionary_ohio_csv_full');
        Cache::forget('dictionary_all_words');
        Cache::forget('dictionary_sowpods');

        for ($length = 3; $length <= 15; $length++) {
            Cache::forget("dictionary_words_{$length}");
        }
    }
}
