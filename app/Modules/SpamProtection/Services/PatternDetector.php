<?php

namespace App\Modules\SpamProtection\Services;

class PatternDetector
{
    protected array $keyboardPatterns = [
        'qwerty',
        'qwertz',
        'asdf',
        'zxcv',
        'qazwsx',
        'rfvtgb',
        'yhnujm',
        'wsxedc',
        'plokij',
        'jhgfds',
        'mnbvcx',
        'poiuyt',
        'lkjhgf',
    ];

    public function check(string $username, string $email): array
    {
        // Check for keyboard mashing patterns
        if ($this->isKeyboardMashing($username)) {
            return [
                'passed' => false,
                'reason' => 'Username appears to be random characters',
                'pattern' => 'keyboard_mashing',
            ];
        }

        // Check for excessive consonants (no vowels)
        if ($this->hasExcessiveConsonants($username)) {
            return [
                'passed' => false,
                'reason' => 'Username contains suspicious character patterns',
                'pattern' => 'excessive_consonants',
            ];
        }

        // Check for repetitive patterns
        if ($this->hasRepetitivePattern($username)) {
            return [
                'passed' => false,
                'reason' => 'Username contains repetitive patterns',
                'pattern' => 'repetitive',
            ];
        }

        // Check for excessive numbers
        if ($this->hasExcessiveNumbers($username)) {
            return [
                'passed' => false,
                'reason' => 'Username contains too many numbers',
                'pattern' => 'excessive_numbers',
            ];
        }

        // Check for username/email similarity patterns common in spam
        if ($this->hasSpamUsernameEmailPattern($username, $email)) {
            return [
                'passed' => false,
                'reason' => 'Registration pattern matches known spam behavior',
                'pattern' => 'spam_correlation',
            ];
        }

        return ['passed' => true];
    }

    protected function isKeyboardMashing(string $text): bool
    {
        $lowerText = strtolower($text);

        // Check for known keyboard patterns
        foreach ($this->keyboardPatterns as $pattern) {
            if (str_contains($lowerText, $pattern)) {
                return true;
            }
        }

        // Check for random-looking character sequences
        if (strlen($text) >= 6) {
            $lettersOnly = preg_replace('/[^a-zA-Z]/', '', $text);

            if (strlen($lettersOnly) >= 4) {
                $consonants = preg_match_all('/[bcdfghjklmnpqrstvwxz]/i', $lettersOnly);
                $vowels = preg_match_all('/[aeiou]/i', $lettersOnly);

                // If consonant to vowel ratio is too high (very random looking)
                if ($vowels > 0 && $consonants / $vowels > 5) {
                    return true;
                }

                // If mostly consonants with no vowels and long enough
                if ($vowels === 0 && $consonants > 5) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasExcessiveConsonants(string $text): bool
    {
        // Remove numbers first
        $lettersOnly = preg_replace('/[0-9]/', '', $text);

        if (strlen($lettersOnly) < 5) {
            return false;
        }

        // Check for sequences of 5+ consonants
        return preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $lettersOnly) === 1;
    }

    protected function hasRepetitivePattern(string $text): bool
    {
        // Check for same character repeated 4+ times
        if (preg_match('/(.)\1{3,}/', $text)) {
            return true;
        }

        // Check for pattern repeated 3+ times (e.g., "abcabcabc", "xyxyxy")
        if (preg_match('/(.{2,4})\1{2,}/', $text)) {
            return true;
        }

        return false;
    }

    protected function hasSpamUsernameEmailPattern(string $username, string $email): bool
    {
        $emailLocal = strtolower(strstr($email, '@', true));
        $userLower = strtolower($username);

        // Pattern: username is random chars + numbers matching email local part
        // Example: username "jkh34kj" with email "jkh34kj@spam.com"
        if ($emailLocal === $userLower && preg_match('/^[a-z]{2,4}[0-9]{2,4}[a-z]{0,2}$/i', $username)) {
            return true;
        }

        return false;
    }

    protected function hasExcessiveNumbers(string $text): bool
    {
        $numbers = preg_match_all('/[0-9]/', $text);
        $letters = preg_match_all('/[a-zA-Z]/', $text);

        $total = $numbers + $letters;

        if ($total === 0) {
            return false;
        }

        // If more than 60% numbers
        if ($numbers / $total > 0.6) {
            return true;
        }

        // If username is mostly numbers with very few letters
        if ($numbers > 6 && $letters < 3) {
            return true;
        }

        return false;
    }
}
