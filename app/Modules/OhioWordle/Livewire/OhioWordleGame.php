<?php

namespace App\Modules\OhioWordle\Livewire;

use App\Modules\OhioWordle\Models\WordleUserStats;
use App\Modules\OhioWordle\Services\WordleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OhioWordleGame extends Component
{
    public $word;

    public $wordLength = 5;

    public $gameState = [
        'guesses' => [],
        'feedback' => [],
        'remainingGuesses' => WordleService::MAX_GUESSES,
        'gameComplete' => false,
        'gameWon' => false,
        'answer' => null,
    ];

    public $keyboardState = [];

    public $errorMessage;

    public $userStats;

    public $wordStats = null;

    public $showWordStats = false;

    public function mount(WordleService $wordleService)
    {
        $this->word = $wordleService->getTodaysWord();

        if (!$this->word) {
            $this->errorMessage = 'No puzzle available for today.';

            return;
        }

        $this->wordLength = $this->word->word_length;
        $this->initializeKeyboard();

        if (Auth::check()) {
            $progress = $wordleService->getUserProgress(Auth::user());

            if ($progress) {
                $this->loadProgress($progress->guesses, $progress->feedback, $progress->attempts, $progress->completed_at, $progress->solved);
                $this->userStats = WordleUserStats::getOrCreateForUser(Auth::id());
            }
        } else {
            $guestProgress = $wordleService->getGuestProgress();

            if ($guestProgress) {
                $this->loadProgress($guestProgress->guesses, $guestProgress->feedback, $guestProgress->attempts, $guestProgress->completed_at, $guestProgress->solved);
            }
        }
    }

    private function initializeKeyboard(): void
    {
        $letters = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        foreach ($letters as $letter) {
            $this->keyboardState[$letter] = 'unused';
        }
    }

    private function loadProgress(array $guesses, array $feedback, int $attempts, $completedAt, bool $solved): void
    {
        $this->gameState['guesses'] = $guesses;
        $this->gameState['feedback'] = $feedback;
        $this->gameState['remainingGuesses'] = WordleService::MAX_GUESSES - $attempts;
        $this->gameState['gameComplete'] = (bool)$completedAt;
        $this->gameState['gameWon'] = $solved;

        // Rebuild keyboard state from previous feedback
        foreach ($guesses as $index => $guess) {
            if (isset($feedback[$index])) {
                $this->updateKeyboardFromFeedback($guess, $feedback[$index]);
            }
        }

        if ($this->gameState['gameComplete']) {
            $this->gameState['answer'] = $this->word->word;
            $this->showWordStats();
        }
    }

    private function updateKeyboardFromFeedback(string $guess, array $feedback): void
    {
        $letters = str_split(strtoupper($guess));

        foreach ($letters as $index => $letter) {
            $status = $feedback[$index];
            $currentStatus = $this->keyboardState[$letter] ?? 'unused';

            // Priority: correct > present > absent > unused
            if ($status === 'correct') {
                $this->keyboardState[$letter] = 'correct';
            } elseif ($status === 'present' && $currentStatus !== 'correct') {
                $this->keyboardState[$letter] = 'present';
            } elseif ($status === 'absent' && $currentStatus === 'unused') {
                $this->keyboardState[$letter] = 'absent';
            }
        }
    }

    public function showWordStats(): void
    {
        $wordleService = app(WordleService::class);
        $this->wordStats = $wordleService->loadWordStats($this->word);
        $this->showWordStats = true;
    }

    public function submitGuess(string $guess, WordleService $wordleService): void
    {
        if ($this->gameState['gameComplete']) {
            $this->errorMessage = 'This game is already complete.';

            return;
        }

        $this->errorMessage = null;

        $user = Auth::check() ? Auth::user() : null;
        $result = $wordleService->processGuess($user, $guess);

        if (!$result['valid']) {
            $this->errorMessage = $result['error'];

            return;
        }

        // Update game state
        $this->gameState['guesses'] = $result['previousGuesses'];
        $this->gameState['feedback'] = $result['allFeedback'];
        $this->gameState['remainingGuesses'] = $result['remainingGuesses'];
        $this->gameState['gameComplete'] = $result['gameComplete'];
        $this->gameState['gameWon'] = $result['gameWon'];

        // Update keyboard state with latest guess feedback
        $this->updateKeyboardFromFeedback($guess, $result['feedback']);

        if ($result['gameComplete']) {
            $this->gameState['answer'] = $result['answer'];

            if (Auth::check()) {
                $this->userStats = WordleUserStats::getOrCreateForUser(Auth::id());
            }

            $this->dispatch('gameCompleted', [
                'won' => $result['gameWon'],
                'guesses' => count($result['previousGuesses']),
            ]);

            $this->showWordStats();
        }
    }

    public function getShareText(): string
    {
        if (!$this->gameState['gameComplete']) {
            return '';
        }

        $guessCount = $this->gameState['gameWon'] ? count($this->gameState['guesses']) : 'X';
        $date = $this->word->publish_date->format('M j, Y');

        $text = "Wordio \n {$date} {$guessCount}/6\n\n";

        foreach ($this->gameState['feedback'] as $feedbackRow) {
            foreach ($feedbackRow as $status) {
                $text .= match ($status) {
                    'correct' => "\u{1F7E9}", // Green square
                    'present' => "\u{1F7E8}", // Yellow square
                    'absent' => "\u{2B1B}",   // Black square
                    default => '',
                };
            }
            $text .= "\n";
        }

        $text .= "\nhttps://ohiochatter.com/wordio";

        return $text;
    }

    public function render()
    {
        return view('livewire.ohio-wordle-game');
    }
}
