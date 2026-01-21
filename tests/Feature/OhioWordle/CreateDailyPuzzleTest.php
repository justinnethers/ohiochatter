<?php

use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordRotationService;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

beforeEach(function () {
    Cache::flush();
});

describe('wordle:create-daily-puzzle', function () {
    it('creates puzzle for today by default', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();

        expect(WordleWord::whereDate('publish_date', today())->exists())->toBeTrue();
    });

    it('creates puzzle for specific date with --date option', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle', ['--date' => '2026-01-20'])
            ->assertSuccessful();

        expect(WordleWord::whereDate('publish_date', '2026-01-20')->exists())->toBeTrue();
    });

    it('adds word to used_words via service', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON']);
            $mock->shouldReceive('addToUsedWords')->with('AKRON')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();

        expect(WordleWord::first()->word)->toBe('AKRON');
    });

    it('fails when no words available', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(false);
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertFailed()
            ->expectsOutput('No words available in ohio.txt');
    });

    it('skips when puzzle already exists for date', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO']);
        });

        WordleWord::factory()->create(['publish_date' => today()]);

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful()
            ->expectsOutput('A puzzle already exists for ' . today()->toDateString() . '. Use --force to overwrite.');

        expect(WordleWord::whereDate('publish_date', today())->count())->toBe(1);
    });

    it('overwrites existing puzzle with --force', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        WordleWord::factory()->withWord('EXISTING')->create(['publish_date' => today()]);

        $this->artisan('wordle:create-daily-puzzle', ['--force' => true])
            ->assertSuccessful();

        expect(WordleWord::whereDate('publish_date', today())->count())->toBe(1);
        expect(WordleWord::whereDate('publish_date', today())->first()->word)->not->toBe('EXISTING');
    });

    it('dry-run does not modify database', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO']);
            $mock->shouldReceive('addToUsedWords')->never();
            $mock->shouldReceive('clearDictionaryCache')->never();
        });

        $this->artisan('wordle:create-daily-puzzle', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        expect(WordleWord::count())->toBe(0);
    });

    it('skips words already in database', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON']);
            $mock->shouldReceive('addToUsedWords')->with('DAYTON')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        // AKRON already exists in database
        WordleWord::factory()->withWord('AKRON')->create(['publish_date' => '2025-01-01']);

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();

        $todaysPuzzle = WordleWord::whereDate('publish_date', today())->first();
        expect($todaysPuzzle->word)->toBe('DAYTON');
    });

    it('fails when all available words already in database', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON']);
        });

        WordleWord::factory()->withWord('AKRON')->create(['publish_date' => '2025-01-01']);
        WordleWord::factory()->withWord('DAYTON')->create(['publish_date' => '2025-01-02']);

        $this->artisan('wordle:create-daily-puzzle')
            ->assertFailed()
            ->expectsOutput('No unused words available. All words in ohio.txt have already been used.');
    });

    it('calculates word_length correctly', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['COLUMBUS']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();

        $puzzle = WordleWord::first();
        expect($puzzle->word)->toBe('COLUMBUS');
        expect($puzzle->word_length)->toBe(8);
    });

    it('warns when word count is low', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON', 'TOLEDO', 'CANTON', 'ELYRIA', 'LORAIN']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful()
            ->expectsOutputToContain('Warning: Only 5 unused words remaining');
    });

    it('sets puzzle as active by default', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();

        expect(WordleWord::first()->is_active)->toBeTrue();
    });

    it('clears dictionary cache after creating puzzle', function () {
        $this->mock(WordRotationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasAvailableWords')->andReturn(true);
            $mock->shouldReceive('getAvailableWords')->andReturn(['AKRON', 'DAYTON']);
            $mock->shouldReceive('addToUsedWords')->once();
            $mock->shouldReceive('clearDictionaryCache')->once();
        });

        $this->artisan('wordle:create-daily-puzzle')
            ->assertSuccessful();
    });

    it('validates date format', function () {
        $this->artisan('wordle:create-daily-puzzle', ['--date' => 'invalid-date'])
            ->assertFailed()
            ->expectsOutput('Invalid date format. Please use YYYY-MM-DD.');
    });
});
