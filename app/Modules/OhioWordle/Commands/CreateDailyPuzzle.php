<?php

namespace App\Modules\OhioWordle\Commands;

use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordRotationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateDailyPuzzle extends Command
{
    protected $signature = 'wordle:create-daily-puzzle
        {--date= : Specific date (YYYY-MM-DD), defaults to today}
        {--dry-run : Preview without creating}
        {--force : Overwrite existing puzzle for date}';

    protected $description = 'Create a daily OhioWordle puzzle from available words';

    public function __construct(
        private readonly WordRotationService $wordRotationService
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $targetDate = $this->getTargetDate();
        if ($targetDate === null) {
            $this->error('Invalid date format. Please use YYYY-MM-DD.');
            return self::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        // Check for existing puzzle
        $existingPuzzle = WordleWord::whereDate('publish_date', $targetDate)->first();
        if ($existingPuzzle && !$isForce) {
            $this->info("A puzzle already exists for {$targetDate->toDateString()}. Use --force to overwrite.");

            Log::info('Puzzle already exists for ' . $targetDate->toDateString(), $existingPuzzle->toArray());
            return self::SUCCESS;
        }

        // Check for available words
        if (!$this->wordRotationService->hasAvailableWords()) {
            $this->error('No words available in ohio.csv');

            return self::FAILURE;
        }

        // Get available words not already in database
        $availableWords = $this->wordRotationService->getAvailableWords();
        $existingWords = WordleWord::pluck('word')->map(fn($w) => strtoupper($w))->toArray();
        $unusedWords = array_values(array_diff($availableWords, $existingWords));

        if (empty($unusedWords)) {
            $this->error('No unused words available. All words in ohio.csv have already been used.');

            return self::FAILURE;
        }

        // Select random word from unused words
        $selectedWord = $unusedWords[array_rand($unusedWords)];

        // Get metadata for the selected word
        $metadata = $this->wordRotationService->getWordMetadata($selectedWord);

        if ($isDryRun) {
            $this->info("[DRY-RUN] Would create puzzle for {$targetDate->toDateString()} with word: {$selectedWord}");
            $this->info("[DRY-RUN] Word length: " . strlen($selectedWord));
            $this->info("[DRY-RUN] Category: " . ($metadata['category'] ?? 'N/A'));
            $this->info("[DRY-RUN] Description: " . ($metadata['description'] ?? 'N/A'));
            $this->info("[DRY-RUN] Would log '{$selectedWord}' to used_words.txt");

            return self::SUCCESS;
        }

        // Delete existing puzzle if forcing
        if ($existingPuzzle && $isForce) {
            $existingPuzzle->delete();
        }

        // Create the puzzle with metadata
        WordleWord::create([
            'word' => $selectedWord,
            'publish_date' => $targetDate,
            'is_active' => true,
            'category' => $metadata['category'] ?? null,
            'hint' => $metadata['description'] ?? null,
        ]);

        // Log the word to used_words.txt (but keep it in ohio.csv for guess validation)
        $this->wordRotationService->addToUsedWords($selectedWord);

        // Clear dictionary cache
        $this->wordRotationService->clearDictionaryCache();

        $this->info("Created puzzle for {$targetDate->toDateString()} with word: {$selectedWord}");

        // Warn if low on unused words
        $remainingUnused = count($unusedWords) - 1; // -1 for the word we just used
        if ($remainingUnused < 7 && $remainingUnused > 0) {
            $this->warn("Warning: Only {$remainingUnused} unused words remaining");
        }

        return self::SUCCESS;
    }

    private function getTargetDate(): ?Carbon
    {
        $dateOption = $this->option('date');

        if ($dateOption === null) {
            return today();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $dateOption)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
