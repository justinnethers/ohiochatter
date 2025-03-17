<?php

namespace App\Actions\BuckEye;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIAnswerCheck
{
    /**
     * Check if a guess is likely correct
     *
     * @param array|string $correctAnswers The correct answer(s)
     * @param string $guess The user's guess
     * @return bool Whether the answer is likely correct
     */
    public function __invoke($correctAnswers, string $guess): bool
    {
        // Convert single answer to array for consistency
        if (!is_array($correctAnswers)) {
            $correctAnswers = [$correctAnswers];
        }

        // Filter out empty answers
        $correctAnswers = array_filter($correctAnswers);

        // Normalize guess
        $guess = trim($guess);

        // Don't bother checking empty guesses
        if (empty($guess)) {
            return false;
        }

        // Generate a cache key based on the inputs
        $cacheKey = $this->generateCacheKey($correctAnswers, $guess);

        // Get cache time from config (default to 1 week if not specified)
        $cacheTime = Config::get('services.openai.cache_time', 604800);

        // Try to get result from cache first
        return Cache::remember($cacheKey, $cacheTime, function () use ($correctAnswers, $guess) {
            return $this->checkWithOpenAI($correctAnswers, $guess);
        });
    }

    /**
     * Generate a consistent cache key based on the inputs
     */
    private function generateCacheKey(array $correctAnswers, string $guess): string
    {
        // Sort answers for consistent cache keys regardless of order
        sort($correctAnswers);

        // Normalize the guess to lowercase to ensure case-insensitive caching
        $normalizedGuess = strtolower(trim($guess));

        // Create a hash of the combined data for a compact cache key
        return 'openai_answer_check:' . md5(implode('|', $correctAnswers) . '|' . $normalizedGuess);
    }

    /**
     * Make the actual OpenAI API call
     */
    protected function checkWithOpenAI(array $correctAnswers, string $guess): bool
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an assistant that evaluates whether an answer is close enough to be considered correct. You always respond with JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($correctAnswers, $guess)
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response->choices[0]->message->content;
            $result = json_decode($content, true);

            // Check if the response is valid and says it's correct
            if (json_last_error() === JSON_ERROR_NONE && isset($result['is_correct'])) {
                return (bool)$result['is_correct'];
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error checking answer with OpenAI', [
                'message' => $e->getMessage(),
                'correct_answers' => $correctAnswers,
                'guess' => $guess
            ]);

            // Fall back to false on any error
            return false;
        }
    }

    /**
     * Build the prompt for OpenAI
     */
    private function buildPrompt(array $correctAnswers, string $guess): string
    {
        $answersJson = json_encode($correctAnswers);

        return <<<PROMPT
I have a word puzzle game where I need to check if a user's guess is close enough to the correct answer(s).

Correct answers: $answersJson
User's guess: "$guess"

Determine if the user's guess is close enough to any of the correct answers, considering:
- Minor misspellings
- Capitalization differences
- Singular/plural forms
- Word order variations
- Regional spelling variations
- Common abbreviations

Respond with a JSON object that only includes:
{"is_correct": true or false}

If the user's guess is semantically similar enough to any of the correct answers to be considered correct, respond with {"is_correct": true}. Otherwise, respond with {"is_correct": false}.
PROMPT;
    }
}
