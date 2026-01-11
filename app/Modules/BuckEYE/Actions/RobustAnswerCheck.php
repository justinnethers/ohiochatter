<?php

namespace App\Modules\BuckEYE\Actions;

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

        // Only use Levenshtein check for answers that are sufficiently similar
        // to avoid false positives with generic answers
        if ($this->shouldUseLevenshteinCheck($correctAnswers, $guess) &&
            $this->levenshteinCheck($correctAnswers, $guess)) {
            return true;
        }

        // If OpenAI is enabled, use it as the last resort
        if (config('services.openai.enabled', false)) {
            // Generate a cache key based on the inputs
            $cacheKey = $this->generateCacheKey($correctAnswers, $guess);

            // Get cache time from config (default to 1 week if not specified)
            $cacheTime = Config::get('services.openai.cache_time', 604800);

            // Try to get result from cache first
            return Cache::remember($cacheKey, $cacheTime, function () use ($correctAnswers, $guess) {
                return $this->checkWithOpenAI($correctAnswers, $guess);
            });
        }

        return false;
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

            // Check singular/plural variations ONLY for exact phrases except for the trailing s
            if (Str::endsWith($normalizedGuess, 's') &&
                rtrim($normalizedGuess, 's') === $normalizedAnswer) {
                return true;
            }

            if (!Str::endsWith($normalizedGuess, 's') &&
                $normalizedGuess . 's' === $normalizedAnswer) {
                return true;
            }

            // Same checks but the other way around
            if (Str::endsWith($normalizedAnswer, 's') &&
                rtrim($normalizedAnswer, 's') === $normalizedGuess) {
                return true;
            }

            if (!Str::endsWith($normalizedAnswer, 's') &&
                $normalizedAnswer . 's' === $normalizedGuess) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if Levenshtein check should be used based on answer/guess similarity
     */
    protected function shouldUseLevenshteinCheck(array $correctAnswers, string $guess): bool
    {
        $normalizedGuess = Str::lower(trim($guess));
        $guessWords = explode(' ', $normalizedGuess);

        // Single-word guesses can use Levenshtein
        if (count($guessWords) === 1) {
            foreach ($correctAnswers as $answer) {
                $normalizedAnswer = Str::lower(trim($answer));
                $answerWords = explode(' ', $normalizedAnswer);

                // If the answer is also a single word, or the guess matches one of the significant words
                if (count($answerWords) === 1 || $this->containsSignificantWord($normalizedGuess, $normalizedAnswer)) {
                    return true;
                }

                // Check similarity for longer words
                foreach ($answerWords as $answerWord) {
                    if (strlen($answerWord) > 4) {
                        // For longer words, check if they're similar enough
                        similar_text(
                            Str::lower($guessWords[0]),
                            Str::lower($answerWord),
                            $percentSimilar
                        );

                        if ($percentSimilar > 70) {
                            return true;
                        }
                    }
                }
            }
        }

        // For multi-word guesses, require more similarity to reduce false positives
        foreach ($correctAnswers as $answer) {
            $normalizedAnswer = Str::lower(trim($answer));

            // Check if at least 50% of words from the answer appear in the guess
            if ($this->wordOverlapPercentage($normalizedGuess, $normalizedAnswer) >= 0.5) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if guess contains at least one significant word from the answer
     */
    protected function containsSignificantWord(string $guess, string $answer): bool
    {
        $answerWords = explode(' ', $answer);
        $stopWords = ['the', 'a', 'an', 'of', 'in', 'on', 'at', 'by', 'for', 'with', 'and', 'or', 'to'];

        foreach ($answerWords as $word) {
            // Skip stop words
            if (in_array($word, $stopWords)) {
                continue;
            }

            // Check if the significant word appears in the guess
            if (str_contains($guess, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate what percentage of words from answer appear in guess
     */
    protected function wordOverlapPercentage(string $guess, string $answer): float
    {
        $guessWords = explode(' ', $guess);
        $answerWords = explode(' ', $answer);
        $stopWords = ['the', 'a', 'an', 'of', 'in', 'on', 'at', 'by', 'for', 'with', 'and', 'or', 'to'];

        // Filter out stop words
        $answerWords = array_filter($answerWords, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords);
        });

        if (empty($answerWords)) {
            return 0;
        }

        $matchCount = 0;

        foreach ($answerWords as $word) {
            if (strlen($word) > 3 && (in_array($word, $guessWords) || str_contains($guess, $word))) {
                $matchCount++;
            }
        }

        return $matchCount / count($answerWords);
    }

    /**
     * Check using Levenshtein distance for typos
     */
    protected function levenshteinCheck(array $correctAnswers, string $guess): bool
    {
        $normalizedGuess = Str::lower(trim($guess));

        foreach ($correctAnswers as $answer) {
            $normalizedAnswer = Str::lower(trim($answer));

            // Simple check for single-word answers and guesses
            if (!str_contains($normalizedGuess, ' ') && !str_contains($normalizedAnswer, ' ')) {
                // Calculate Levenshtein distance for single-word answers
                $distance = levenshtein($normalizedGuess, $normalizedAnswer);

                // Allow up to 2 character differences for longer words
                $maxAllowedDistance = strlen($normalizedAnswer) <= 4 ? 1 : 2;

                if ($distance <= $maxAllowedDistance) {
                    return true;
                }
            } // Check for typos in each word for multi-word answers and guesses, but only if they're similar
            else if (abs(str_word_count($normalizedGuess) - str_word_count($normalizedAnswer)) <= 1) {
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

        // If the word counts are too different, it's probably not a match
        if (abs(count($guessWords) - count($answerWords)) > 1) {
            return false;
        }

        // Check each word against each other word to find potential matches
        $matchedGuessWords = [];
        $matchedAnswerWords = [];
        $stopWords = ['the', 'a', 'an', 'of', 'in', 'on', 'at', 'by', 'for', 'with', 'and', 'or', 'to'];

        foreach ($guessWords as $i => $guessWord) {
            // Skip stop words in guess
            if (in_array(strtolower($guessWord), $stopWords)) {
                $matchedGuessWords[] = $i;
                continue;
            }

            foreach ($answerWords as $j => $answerWord) {
                // Skip already matched answer words
                if (in_array($j, $matchedAnswerWords)) {
                    continue;
                }

                // Skip stop words in answer - don't count them as matches
                if (in_array(strtolower($answerWord), $stopWords)) {
                    continue;
                }

                // Adjust distance allowance based on word length
                // Allow up to 2 characters to be wrong for longer words
                $maxAllowedDistance = strlen($answerWord) <= 4 ? 1 : 2;

                $distance = levenshtein($guessWord, $answerWord);
                if ($distance <= $maxAllowedDistance) {
                    $matchedGuessWords[] = $i;
                    $matchedAnswerWords[] = $j;
                    break;
                }
            }
        }

        // Filter out stop words for calculating required matches
        $significantAnswerWords = array_filter($answerWords, function ($word) use ($stopWords) {
            return !in_array(strtolower($word), $stopWords);
        });

        // Calculate how many significant words matched
        $matchedWordCount = count($matchedAnswerWords);

        // Require at least 75% of significant words to match
        $requiredMatchCount = ceil(count($significantAnswerWords) * 0.75);

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
                        'content' => 'You are an assistant that evaluates whether an answer is close enough to be considered correct for a word puzzle game. You should be strict in evaluation, not allowing generic answers. You always respond with JSON.'
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

Be VERY STRICT when determining if the user's guess is close enough to any of the correct answers.
- Generic answers (like "a bridge" for "Roebling Suspension Bridge") should NEVER be accepted
- The guess must contain specific identifying words from the correct answer
- Minor typos in key words can be accepted (up to 2 letters wrong in longer words)
- Variations in word order may be accepted only if all key words are present

Respond with a JSON object that only includes:
{"is_correct": true or false}

Example 1: If the correct answer is "Roebling Suspension Bridge" then "a bridge" should be INCORRECT.
Example 2: If the correct answer is "Roebling Suspension Bridge" then "Reobling Bridge" could be correct (typo in key term).
Example 3: If the correct answer is "Roebling Suspension Bridge" then "Bridge Suspension Roebling" could be correct (all key terms present).
PROMPT;
    }
}
