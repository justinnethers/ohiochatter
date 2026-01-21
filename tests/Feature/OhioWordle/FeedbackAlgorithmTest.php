<?php

use App\Modules\OhioWordle\Services\WordioService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->wordleService = app(WordioService::class);
});

describe('calculateFeedback - basic matching', function () {
    it('marks all letters as correct when guess matches answer', function () {
        $feedback = $this->wordleService->calculateFeedback('AKRON', 'AKRON');

        expect($feedback)->toBe(['correct', 'correct', 'correct', 'correct', 'correct']);
    });

    it('marks all letters as absent when no matches', function () {
        $feedback = $this->wordleService->calculateFeedback('XXXXX', 'AKRON');

        expect($feedback)->toBe(['absent', 'absent', 'absent', 'absent', 'absent']);
    });

    it('marks letter as correct when in right position', function () {
        $feedback = $this->wordleService->calculateFeedback('ACTOR', 'AKRON');

        expect($feedback[0])->toBe('correct'); // A in position 0
    });

    it('marks letter as present when in word but wrong position', function () {
        $feedback = $this->wordleService->calculateFeedback('RONAK', 'AKRON');

        expect($feedback[0])->toBe('present'); // R is in word but not at position 0
        expect($feedback[1])->toBe('present'); // O is in word but not at position 1
        expect($feedback[2])->toBe('present'); // N is in word but not at position 2
        expect($feedback[3])->toBe('present'); // A is in word but not at position 3
        expect($feedback[4])->toBe('present'); // K is in word but not at position 4
    });

    it('marks letter as absent when not in word', function () {
        $feedback = $this->wordleService->calculateFeedback('AXRON', 'AKRON');

        expect($feedback[1])->toBe('absent'); // X is not in AKRON
    });
});

describe('calculateFeedback - duplicate letter handling', function () {
    it('marks only one present when guess has duplicate but answer has single', function () {
        // Answer: AKRON (one O)
        // Guess:  OZONE (two O's)
        // First O should be present, second O should be absent
        $feedback = $this->wordleService->calculateFeedback('OZONE', 'AKRON');

        expect($feedback[0])->toBe('present'); // First O - present
        expect($feedback[2])->toBe('absent');  // Second O - no more O's available
    });

    it('marks correct before present when handling duplicates', function () {
        // Answer: ROBOT (two O's at positions 1 and 3)
        // Guess:  OOZOO (four O's at positions 0, 1, 3, 4)
        // Positions 1 and 3 are correct matches, consuming both O's
        // Positions 0 and 4 have no O's left to match against
        $feedback = $this->wordleService->calculateFeedback('OOZOO', 'ROBOT');

        expect($feedback[0])->toBe('absent');  // O - no O's available (both used by correct matches)
        expect($feedback[1])->toBe('correct'); // O - correct (at pos 1 in answer)
        expect($feedback[2])->toBe('absent');  // Z - not in word
        expect($feedback[3])->toBe('correct'); // O - correct (at pos 3 in answer)
        expect($feedback[4])->toBe('absent');  // O - no more O's available
    });

    it('handles word with all same letters', function () {
        $feedback = $this->wordleService->calculateFeedback('AAAAA', 'AKRON');

        expect($feedback[0])->toBe('correct'); // A at position 0
        expect($feedback[1])->toBe('absent');  // No more A's in answer
        expect($feedback[2])->toBe('absent');
        expect($feedback[3])->toBe('absent');
        expect($feedback[4])->toBe('absent');
    });

    it('prioritizes correct position over present for same letter', function () {
        // Answer: ALARM (A at 0 and 2)
        // Guess:  ALPHA (A at 0 and 4)
        // Position 0: correct (A matches)
        // Position 4: present (A exists at position 2)
        $feedback = $this->wordleService->calculateFeedback('ALPHA', 'ALARM');

        expect($feedback[0])->toBe('correct'); // A correct at position 0
        expect($feedback[4])->toBe('present'); // A present (exists at position 2)
    });
});

describe('calculateFeedback - variable word lengths', function () {
    it('handles 4-letter words', function () {
        $feedback = $this->wordleService->calculateFeedback('OHIO', 'OHIO');

        expect($feedback)->toBe(['correct', 'correct', 'correct', 'correct']);
        expect($feedback)->toHaveCount(4);
    });

    it('handles 6-letter words', function () {
        $feedback = $this->wordleService->calculateFeedback('DAYTON', 'DAYTON');

        expect($feedback)->toBe(['correct', 'correct', 'correct', 'correct', 'correct', 'correct']);
        expect($feedback)->toHaveCount(6);
    });

    it('handles 10-letter words', function () {
        $feedback = $this->wordleService->calculateFeedback('CINCINNATI', 'CINCINNATI');

        expect($feedback)->toHaveCount(10);
        expect($feedback)->each->toBe('correct');
    });

    it('handles mixed results with longer words', function () {
        // COLUMBUS vs CLEVELAND
        $feedback = $this->wordleService->calculateFeedback('COLUMBUS', 'CLEVELAND');

        expect($feedback)->toHaveCount(8);
        expect($feedback[0])->toBe('correct');  // C
        expect($feedback[1])->toBe('absent');   // O not in CLEVELAND
        expect($feedback[2])->toBe('present');  // L present but wrong position
        expect($feedback[3])->toBe('absent');   // U not in CLEVELAND
        expect($feedback[4])->toBe('absent');   // M not in CLEVELAND
        expect($feedback[5])->toBe('absent');   // B not in CLEVELAND
        expect($feedback[6])->toBe('absent');   // U not in CLEVELAND
        expect($feedback[7])->toBe('absent');   // S not in CLEVELAND
    });
});

describe('calculateFeedback - case insensitivity', function () {
    it('treats lowercase and uppercase as equivalent', function () {
        $feedback = $this->wordleService->calculateFeedback('akron', 'AKRON');

        expect($feedback)->toBe(['correct', 'correct', 'correct', 'correct', 'correct']);
    });

    it('handles mixed case', function () {
        $feedback = $this->wordleService->calculateFeedback('AkRoN', 'akron');

        expect($feedback)->toBe(['correct', 'correct', 'correct', 'correct', 'correct']);
    });
});

describe('calculateFeedback - edge cases', function () {
    it('returns empty array for empty strings', function () {
        $feedback = $this->wordleService->calculateFeedback('', '');

        expect($feedback)->toBe([]);
    });

    it('handles single letter words', function () {
        $feedback = $this->wordleService->calculateFeedback('A', 'A');

        expect($feedback)->toBe(['correct']);
    });

    it('handles single letter mismatch', function () {
        $feedback = $this->wordleService->calculateFeedback('A', 'B');

        expect($feedback)->toBe(['absent']);
    });
});
