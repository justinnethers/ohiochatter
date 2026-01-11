<?php

use App\Models\User;
use App\Modules\BuckEYE\Livewire\BuckEyeUserStats;
use App\Modules\BuckEYE\Models\UserGameStats;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('rendering', function () {
    it('renders for authenticated user', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class)
            ->assertStatus(200);
    });

    it('renders view', function () {
        Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class)
            ->assertViewIs('livewire.buck-eye-user-stats');
    });
});

describe('stats loading', function () {
    it('loads user stats on mount', function () {
        UserGameStats::factory()->for($this->user)->withGames(10, 7)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        expect($component->get('userStats.games_played'))->toBe(10);
        expect($component->get('userStats.games_won'))->toBe(7);
    });

    it('creates stats if none exist', function () {
        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        expect($component->get('userStats'))->not->toBeNull();
    });

    it('does not load stats for guest', function () {
        $component = Livewire::test(BuckEyeUserStats::class);

        expect($component->get('userStats'))->toBeNull();
    });
});

describe('stats display', function () {
    it('displays streak information', function () {
        UserGameStats::factory()->for($this->user)->withStreak(5, 10)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        expect($component->get('userStats.current_streak'))->toBe(5);
        expect($component->get('userStats.max_streak'))->toBe(10);
    });

    it('displays guess distribution', function () {
        UserGameStats::factory()->for($this->user)->create([
            'guess_distribution' => [1 => 2, 2 => 5, 3 => 10, 4 => 3, 5 => 1],
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        expect($component->get('userStats.guess_distribution'))->toBeArray();
        expect($component->get('userStats.guess_distribution.3'))->toBe(10);
    });
});

describe('stat refresh', function () {
    it('refreshes stats on gameCompleted event', function () {
        $stats = UserGameStats::factory()->for($this->user)->withGames(5, 3)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        // Verify initial stats
        expect($component->get('userStats.games_played'))->toBe(5);

        // Update stats in database
        $stats->update(['games_played' => 6, 'games_won' => 4]);

        // Trigger refresh via event
        $component->dispatch('gameCompleted');

        // Verify stats were refreshed
        expect($component->get('userStats.games_played'))->toBe(6);
        expect($component->get('userStats.games_won'))->toBe(4);
    });

    it('can manually refresh stats', function () {
        $stats = UserGameStats::factory()->for($this->user)->withGames(5, 3)->create();

        $component = Livewire::actingAs($this->user)
            ->test(BuckEyeUserStats::class);

        $stats->update(['games_played' => 10]);

        $component->call('refreshStats');

        expect($component->get('userStats.games_played'))->toBe(10);
    });
});
