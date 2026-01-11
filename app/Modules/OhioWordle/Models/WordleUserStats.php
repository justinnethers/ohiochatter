<?php

namespace App\Modules\OhioWordle\Models;

use App\Models\User;
use Database\Factories\WordleUserStatsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordleUserStats extends Model
{
    use HasFactory;

    protected $table = 'wordle_user_stats';

    protected $guarded = [];

    protected $casts = [
        'guess_distribution' => 'array',
        'last_played_date' => 'date',
    ];

    protected $attributes = [
        'guess_distribution' => '{}',
    ];

    protected static function newFactory(): WordleUserStatsFactory
    {
        return WordleUserStatsFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create stats for a user.
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'games_played' => 0,
                'games_won' => 0,
                'current_streak' => 0,
                'max_streak' => 0,
                'guess_distribution' => [],
            ]
        );
    }

    /**
     * Update stats after a game.
     */
    public function updateAfterGame(bool $won, ?int $guesses = null): void
    {
        $this->games_played++;

        if ($won) {
            $this->games_won++;
            $this->current_streak++;
            $this->max_streak = max($this->max_streak, $this->current_streak);

            // Update guess distribution
            if ($guesses) {
                $distribution = $this->guess_distribution ?? [];
                $key = (string) $guesses;
                $distribution[$key] = ($distribution[$key] ?? 0) + 1;
                $this->guess_distribution = $distribution;
            }
        } else {
            $this->current_streak = 0;
        }

        $this->last_played_date = now()->toDateString();
        $this->save();
    }
}
