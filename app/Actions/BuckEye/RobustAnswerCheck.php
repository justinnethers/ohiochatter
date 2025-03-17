<?php

namespace App\Actions\BuckEye;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class RobustAnswerCheck
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

        // First do a simple check for exact matches and common variations
        if ($this->simpleVariationCheck($correctAnswers, $guess)) {
            return true;
        }

        // Then try the Levenshtein distance check for typos
        if ($this->levenshteinCheck($correctAnswers, $guess)) {
            return true;
        }

        // Finally, use OpenAI as the last resort (most expensive but most flexible)
        if (!config('services.openai.enabled', false)) {
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
     * Simple check for exact matches and common variations (singular/plural)
     */
    protected function simpleVariationCheck(array $correctAnswers, string $guess): bool
    {
        $normalizedGuess = Str::lower(trim($guess));

        foreach ($correctAnswers as $answer) {
            $normalizedAnswer = Str::lower(trim($answer));

            // Check exact match
            if ($normalizedGuess === $normalizedAnswer) {
                return true;
            }

            // Check singular/plural variations
            // If answer ends with 's', check without 's'
            if (Str::endsWith($normalizedGuess, 's') && rtrim($normalizedGuess, 's') === $normalizedAnswer) {
                return true;
            }

            // If answer doesn't end with 's', check with 's'
            if (!Str::endsWith($normalizedGuess, 's') && $normalizedGuess . 's' === $normalizedAnswer) {
                return true;
            }

            // Same checks but the other way around (for the answer)
            if (Str::endsWith($normalizedAnswer, 's') && rtrim($normalizedAnswer, 's') === $normalizedGuess) {
                return true;
            }

            if (!Str::endsWith($normalizedAnswer, 's') && $normalizedAnswer . 's' === $normalizedGuess) {
                return true;
            }

            // Word order check
            if ($this->hasSameWords($normalizedGuess, $normalizedAnswer)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two strings have the same words regardless of order
     */
    protected function hasSameWords(string $str1, string $str2): bool
    {
        $words1 = collect(explode(' ', $str1))->filter()->sort()->values()->toArray();
        $words2 = collect(explode(' ', $str2))->filter()->sort()->values()->toArray();

        return $words1 === $words2;
    }

    /**
     * Check using Levenshtein distance for typos
     */
    protected function levenshteinCheck(array $correctAnswers, string $guess): bool
    {
        $normalizedGuess = Str::lower(trim($guess));

        foreach ($correctAnswers as $answer) {
            $normalizedAnswer = Str::lower(trim($answer));

            // Calculate Levenshtein distance
            $distance = levenshtein($normalizedGuess, $normalizedAnswer);

            // For short answers, allow 1 typo, for longer ones allow more
            $maxAllowedDistance = floor(strlen($normalizedAnswer) * 0.2); // 20% of the length
            $maxAllowedDistance = max(1, min(3, $maxAllowedDistance)); // Between 1 and 3

            if ($distance <= $maxAllowedDistance) {
                return true;
            }

            // Also check with variation for singular/plural
            $singularPlural = [
                rtrim($normalizedAnswer, 's'),
                $normalizedAnswer . 's'
            ];

            foreach ($singularPlural as $variation) {
                $distance = levenshtein($normalizedGuess, $variation);
                if ($distance <= $maxAllowedDistance) {
                    return true;
                }
            }

            // Check for typos in each word for multi-word answers and guesses
            if (str_contains($normalizedGuess, ' ') && str_contains($normalizedAnswer, ' ')) {
                if ($this->multiWordLevenshteinCheck($normalizedGuess, $normalizedAnswer)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check for typos in individual words of multi-word phrases
     */
    protected function multiWordLevenshteinCheck(string $guess, string $answer): bool
    {
        $guessWords = explode(' ', $guess);
        $answerWords = explode(' ', $answer);

        // If the word counts are very different, it's probably not a match
        if (abs(count($guessWords) - count($answerWords)) > 1) {
            return false;
        }

        // Check each word against each other word to find potential matches
        $matchedGuessWords = [];
        $matchedAnswerWords = [];

        foreach ($guessWords as $i => $guessWord) {
            foreach ($answerWords as $j => $answerWord) {
                // Skip already matched answer words
                if (in_array($j, $matchedAnswerWords)) {
                    continue;
                }

                // Calculate distance
                $distance = levenshtein($guessWord, $answerWord);
                $maxAllowedDistance = floor(strlen($answerWord) * 0.3); // Allow more typos in individual words
                $maxAllowedDistance = max(1, min(2, $maxAllowedDistance)); // Between 1 and 2

                if ($distance <= $maxAllowedDistance) {
                    $matchedGuessWords[] = $i;
                    $matchedAnswerWords[] = $j;
                    break;
                }
            }
        }

        // Calculate how many words matched
        $matchedWordCount = count($matchedGuessWords);
        $requiredMatchCount = min(count($guessWords), count($answerWords)) - 1; // Allow one word to not match

        return $matchedWordCount >= $requiredMatchCount;
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
                        'content' => 'You are an assistant that evaluates whether an answer is close enough to be considered correct for a word puzzle game. You should be extremely lenient with typos, pluralization, and word variations. You always respond with JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($correctAnswers, $guess)
                    ]
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2, // Lower temperature for more consistent responses
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

Be EXTREMELY LENIENT when determining if the user's guess is close enough to any of the correct answers. Consider all of the following to be valid variations:
- Misspellings (even with multiple typos like "serpet" instead of "serpent")
- Missing or extra letters
- Capitalization differences
- Singular/plural forms (e.g. "mound" vs "mounds")
- Word order variations
- Missing or extra spaces
- Regional spelling variations
- Common abbreviations
- Missing or extra words that don't change the core meaning

Respond with a JSON object that only includes:
{"is_correct": true or false}

If the user's guess captures the essential meaning of any correct answer despite errors, respond with {"is_correct": true}. Be extremely generous in your assessment - it's much better to accept a slightly wrong answer than to reject a nearly correct one.

Example 1: If the correct answer is "serpent mound" then "serpet mounds", "serpant mound", "mound of serpent" should all be considered correct.
Example 2: If the correct answer is "lake erie", then "lack eerie", "lak eri", "erie lake" should all be considered correct.
PROMPT;
    }
}
