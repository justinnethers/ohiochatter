<?php

use App\Modules\BuckEYE\Actions\RobustAnswerCheck;

beforeEach(function () {
    $this->checker = app(RobustAnswerCheck::class);
    // Disable OpenAI for testing
    config(['services.openai.enabled' => false]);
});

describe('exact matching', function () {
    it('returns true for exact match', function () {
        $result = ($this->checker)(['Ohio Stadium'], 'Ohio Stadium');

        expect($result)->toBeTrue();
    });

    it('returns true for case-insensitive match', function () {
        $result = ($this->checker)(['Ohio Stadium'], 'ohio stadium');

        expect($result)->toBeTrue();
    });

    it('returns true for uppercase match', function () {
        $result = ($this->checker)(['Ohio Stadium'], 'OHIO STADIUM');

        expect($result)->toBeTrue();
    });

    it('returns false for empty guess', function () {
        $result = ($this->checker)(['Ohio Stadium'], '');

        expect($result)->toBeFalse();
    });

    it('handles single string answer', function () {
        $result = ($this->checker)('Ohio Stadium', 'Ohio Stadium');

        expect($result)->toBeTrue();
    });

    it('returns false for completely wrong answer', function () {
        $result = ($this->checker)(['Ohio Stadium'], 'Wrong Answer');

        expect($result)->toBeFalse();
    });

    it('trims whitespace from guess', function () {
        $result = ($this->checker)(['Ohio Stadium'], '  Ohio Stadium  ');

        expect($result)->toBeTrue();
    });
});

describe('plural variations', function () {
    it('accepts singular when plural is answer', function () {
        $result = ($this->checker)(['Buckeyes'], 'Buckeye');

        expect($result)->toBeTrue();
    });

    it('accepts plural when singular is answer', function () {
        $result = ($this->checker)(['Buckeye'], 'Buckeyes');

        expect($result)->toBeTrue();
    });

    it('handles trailing s variations case insensitively', function () {
        $result = ($this->checker)(['Ohio Cats'], 'ohio cat');

        expect($result)->toBeTrue();
    });
});

describe('Levenshtein distance - single words', function () {
    it('accepts 1 character typo for short words', function () {
        $result = ($this->checker)(['Ohio'], 'Ohia');

        expect($result)->toBeTrue();
    });

    it('accepts 2 character typos for longer words', function () {
        $result = ($this->checker)(['Columbus'], 'Columbas');

        expect($result)->toBeTrue();
    });

    it('accepts 2 character typos for Cincinnati', function () {
        $result = ($this->checker)(['Cincinnati'], 'Cincinatti');

        expect($result)->toBeTrue();
    });

    it('rejects too many typos', function () {
        $result = ($this->checker)(['Ohio'], 'Oxyz');

        expect($result)->toBeFalse();
    });

    it('rejects very different single words', function () {
        $result = ($this->checker)(['Columbus'], 'Cleveland');

        expect($result)->toBeFalse();
    });
});

describe('Levenshtein distance - multi-word', function () {
    it('accepts typos in individual words', function () {
        $result = ($this->checker)(['Roebling Suspension Bridge'], 'Robling Suspension Bridge');

        expect($result)->toBeTrue();
    });

    it('accepts typo in key word', function () {
        $result = ($this->checker)(['Cedar Point'], 'Ceder Point');

        expect($result)->toBeTrue();
    });

    it('requires significant word overlap', function () {
        $result = ($this->checker)(['Roebling Suspension Bridge'], 'A Bridge');

        expect($result)->toBeFalse();
    });

    it('rejects generic answers for specific phrases', function () {
        $result = ($this->checker)(['The Ohio State University'], 'University');

        expect($result)->toBeFalse();
    });
});

describe('stop words', function () {
    it('ignores stop words in comparison', function () {
        $result = ($this->checker)(['The Ohio State University'], 'Ohio State University');

        expect($result)->toBeTrue();
    });

    it('accepts answer with stop words added', function () {
        $result = ($this->checker)(['Ohio State University'], 'The Ohio State University');

        expect($result)->toBeTrue();
    });
});

describe('multiple correct answers', function () {
    it('accepts any of multiple correct answers', function () {
        $answers = ['Ohio Stadium', 'The Shoe', 'Horseshoe'];

        expect(($this->checker)($answers, 'Ohio Stadium'))->toBeTrue();
        expect(($this->checker)($answers, 'The Shoe'))->toBeTrue();
        expect(($this->checker)($answers, 'Horseshoe'))->toBeTrue();
    });

    it('accepts typo in any alternate answer', function () {
        $answers = ['Ohio Stadium', 'The Shoe', 'Horseshoe'];

        expect(($this->checker)($answers, 'Horsesho'))->toBeTrue();
    });

    it('rejects wrong answer even with multiple correct options', function () {
        $answers = ['Ohio Stadium', 'The Shoe', 'Horseshoe'];

        expect(($this->checker)($answers, 'Wrong Answer'))->toBeFalse();
    });
});

describe('edge cases', function () {
    it('filters empty answers from array', function () {
        $result = ($this->checker)(['', 'Ohio', ''], 'Ohio');

        expect($result)->toBeTrue();
    });

    it('handles whitespace only guess', function () {
        $result = ($this->checker)(['Ohio'], '   ');

        expect($result)->toBeFalse();
    });

    it('handles special characters in answer', function () {
        $result = ($this->checker)(["Wendy's"], "Wendy's");

        expect($result)->toBeTrue();
    });

    it('handles numbers in answer', function () {
        $result = ($this->checker)(['Route 66'], 'Route 66');

        expect($result)->toBeTrue();
    });
});

describe('word similarity requirements', function () {
    it('requires at least 50% word overlap for multi-word guesses', function () {
        // "Rock Hall" has 2 words, answer has 5 significant words
        // This should fail since not enough overlap
        $result = ($this->checker)(['Rock and Roll Hall of Fame'], 'Rock Hall');

        expect($result)->toBeFalse();
    });

    it('accepts exact answer for complex phrases', function () {
        $result = ($this->checker)(['Rock and Roll Hall of Fame'], 'Rock and Roll Hall of Fame');

        expect($result)->toBeTrue();
    });

    it('accepts case insensitive complex phrases', function () {
        $result = ($this->checker)(['Rock and Roll Hall of Fame'], 'rock and roll hall of fame');

        expect($result)->toBeTrue();
    });
});

describe('real-world Ohio answers', function () {
    it('handles Cedar Point', function () {
        expect(($this->checker)(['Cedar Point'], 'Cedar Point'))->toBeTrue();
        expect(($this->checker)(['Cedar Point'], 'cedar point'))->toBeTrue();
        expect(($this->checker)(['Cedar Point'], 'Ceder Point'))->toBeTrue();
    });

    it('handles LeBron James', function () {
        expect(($this->checker)(['LeBron James'], 'LeBron James'))->toBeTrue();
        expect(($this->checker)(['LeBron James'], 'lebron james'))->toBeTrue();
        expect(($this->checker)(['LeBron James'], 'Lebron James'))->toBeTrue();
    });

    it('handles Cincinnati Reds', function () {
        expect(($this->checker)(['Cincinnati Reds'], 'Cincinnati Reds'))->toBeTrue();
        expect(($this->checker)(['Cincinnati Reds'], 'cincinatti reds'))->toBeTrue();
    });

    it('handles Wright Brothers', function () {
        expect(($this->checker)(['Wright Brothers'], 'Wright Brothers'))->toBeTrue();
        expect(($this->checker)(['Wright Brothers'], 'Wright Brother'))->toBeTrue();
        expect(($this->checker)(['Wright Brothers'], 'The Wright Brothers'))->toBeTrue();
    });

    it('handles Hocking Hills', function () {
        expect(($this->checker)(['Hocking Hills'], 'Hocking Hills'))->toBeTrue();
        expect(($this->checker)(['Hocking Hills'], 'hocking hills'))->toBeTrue();
        expect(($this->checker)(['Hocking Hills'], 'Hockin Hills'))->toBeTrue();
    });
});
