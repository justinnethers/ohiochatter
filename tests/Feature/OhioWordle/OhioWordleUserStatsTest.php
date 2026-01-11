<?php

namespace Tests\Feature\OhioWordle;

use App\Models\User;
use App\Modules\OhioWordle\Livewire\OhioWordleUserStats;
use App\Modules\OhioWordle\Models\WordleUserStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OhioWordleUserStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_component_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(OhioWordleUserStats::class)
            ->assertStatus(200)
            ->assertSee('Games Played');
    }

    public function test_stats_component_shows_user_statistics(): void
    {
        $user = User::factory()->create();

        WordleUserStats::create([
            'user_id' => $user->id,
            'games_played' => 10,
            'games_won' => 8,
            'current_streak' => 3,
            'max_streak' => 5,
            'guess_distribution' => ['1' => 1, '2' => 2, '3' => 3, '4' => 1, '5' => 1],
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleUserStats::class)
            ->assertSee('10') // games played
            ->assertSee('80%') // win percentage
            ->assertSee('3') // current streak
            ->assertSee('5'); // max streak
    }

    public function test_stats_component_shows_guess_distribution(): void
    {
        $user = User::factory()->create();

        WordleUserStats::create([
            'user_id' => $user->id,
            'games_played' => 5,
            'games_won' => 5,
            'current_streak' => 5,
            'max_streak' => 5,
            'guess_distribution' => ['1' => 1, '2' => 1, '3' => 2, '4' => 1],
        ]);

        Livewire::actingAs($user)
            ->test(OhioWordleUserStats::class)
            ->assertSee('Guess Distribution');
    }

    public function test_stats_refresh_on_game_completed_event(): void
    {
        $user = User::factory()->create();

        $stats = WordleUserStats::create([
            'user_id' => $user->id,
            'games_played' => 5,
            'games_won' => 3,
            'current_streak' => 1,
            'max_streak' => 2,
            'guess_distribution' => [],
        ]);

        $component = Livewire::actingAs($user)
            ->test(OhioWordleUserStats::class);

        // Update stats in background
        $stats->update(['games_played' => 6, 'games_won' => 4]);

        // Trigger refresh
        $component->dispatch('gameCompleted', ['won' => true, 'guesses' => 3]);

        // Stats should be refreshed
        $component->assertSee('6'); // updated games played
    }

    public function test_new_user_sees_empty_stats_message(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(OhioWordleUserStats::class)
            ->assertSee('0') // games played
            ->assertSee('0%'); // win percentage
    }
}
