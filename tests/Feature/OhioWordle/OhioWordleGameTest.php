<?php

namespace Tests\Feature\OhioWordle;

use App\Models\User;
use App\Modules\OhioWordle\Livewire\OhioWordleGame;
use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\DictionaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class OhioWordleGameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the dictionary service to accept all 5-letter words
        $mockDictionary = Mockery::mock(DictionaryService::class);
        $mockDictionary->shouldReceive('isValidWord')
            ->andReturnUsing(function ($word, $length) {
                return strlen($word) === $length && ctype_alpha($word);
            });

        $this->app->instance(DictionaryService::class, $mockDictionary);
    }

    public function test_game_component_renders(): void
    {
        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::test(OhioWordleGame::class)
            ->assertStatus(200)
            ->assertSee('Guesses remaining');
    }

    public function test_game_shows_message_when_no_puzzle_available(): void
    {
        Livewire::test(OhioWordleGame::class)
            ->assertSeeHtml('No puzzle available for today');
    }

    public function test_user_can_add_letter(): void
    {
        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::test(OhioWordleGame::class)
            ->call('addLetter', 'A')
            ->assertSet('currentGuess', 'A')
            ->call('addLetter', 'K')
            ->assertSet('currentGuess', 'AK');
    }

    public function test_user_can_remove_letter(): void
    {
        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::test(OhioWordleGame::class)
            ->call('addLetter', 'A')
            ->call('addLetter', 'K')
            ->call('removeLetter')
            ->assertSet('currentGuess', 'A');
    }

    public function test_user_cannot_add_more_letters_than_word_length(): void
    {
        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::test(OhioWordleGame::class)
            ->call('addLetter', 'A')
            ->call('addLetter', 'K')
            ->call('addLetter', 'R')
            ->call('addLetter', 'O')
            ->call('addLetter', 'N')
            ->call('addLetter', 'X') // Should be ignored
            ->assertSet('currentGuess', 'AKRON');
    }

    public function test_authenticated_user_can_submit_correct_guess(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'AKRON')
            ->call('submitGuess')
            ->assertSet('gameState.gameComplete', true)
            ->assertSet('gameState.gameWon', true);
    }

    public function test_incorrect_guess_updates_game_state(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'AUDIO')
            ->call('submitGuess')
            ->assertSet('gameState.gameComplete', false)
            ->assertSet('gameState.remainingGuesses', 5);
    }

    public function test_keyboard_state_updates_after_guess(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'AUDIO')
            ->call('submitGuess');

        $keyboardState = $component->get('keyboardState');

        // A is at position 0 in both AUDIO and AKRON, so it's correct
        $this->assertEquals('correct', $keyboardState['A']);

        // U, D, I should be absent (not in word)
        $this->assertEquals('absent', $keyboardState['U']);
        $this->assertEquals('absent', $keyboardState['D']);
        $this->assertEquals('absent', $keyboardState['I']);

        // O is in AKRON but not at position 4, so present
        $this->assertEquals('present', $keyboardState['O']);
    }

    public function test_game_ends_after_six_incorrect_guesses(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($user)
            ->test(OhioWordleGame::class);

        // Make 6 incorrect guesses
        $wrongGuesses = ['AUDIO', 'LIGHT', 'COULD', 'FOUND', 'WRITE', 'QUITE'];

        foreach ($wrongGuesses as $guess) {
            $component->set('currentGuess', $guess)->call('submitGuess');
        }

        $component
            ->assertSet('gameState.gameComplete', true)
            ->assertSet('gameState.gameWon', false)
            ->assertSet('gameState.answer', 'AKRON');
    }

    public function test_share_text_generated_correctly_on_win(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'AKRON')
            ->call('submitGuess');

        $shareText = $component->invade()->getShareText();

        $this->assertStringContainsString('Wordio', $shareText);
        $this->assertStringContainsString('1/6', $shareText);
    }

    public function test_invalid_word_shows_error_message(): void
    {
        $user = User::factory()->create();

        // Reset the mock to reject XXXXX
        $mockDictionary = Mockery::mock(DictionaryService::class);
        $mockDictionary->shouldReceive('isValidWord')
            ->with('XXXXX', 5)
            ->andReturn(false);
        $this->app->instance(DictionaryService::class, $mockDictionary);

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'XXXXX')
            ->call('submitGuess')
            ->assertSet('errorMessage', "'XXXXX' is not a valid word");
    }

    public function test_wrong_length_shows_error_message(): void
    {
        $user = User::factory()->create();

        WordleWord::factory()->create([
            'word' => 'AKRON',
            'publish_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleGame::class)
            ->set('currentGuess', 'HI')
            ->call('submitGuess')
            ->assertSet('errorMessage', 'Guess must be 5 letters');
    }
}
