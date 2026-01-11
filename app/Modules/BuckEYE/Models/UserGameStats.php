<?php

namespace App\Modules\BuckEYE\Models;

use App\Models\User;
use Database\Factories\UserGameStatsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGameStats extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return UserGameStatsFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'games_played',
        'games_won',
        'current_streak',
        'max_streak',
        'guess_distribution',
        'last_played_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'guess_distribution' => 'array',
        'last_played_date' => 'date',
    ];

    /**
     * Get the user that owns the stats
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create stats for a user
     *
     * @param int $userId
     * @return UserGameStats
     */
    public static function getOrCreateForUser($userId)
    {
        return self::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Update stats after a game is completed
     *
     * @param bool $won Whether the game was won
     * @param int $guesses Number of guesses taken
     * @return void
     */
    public function updateAfterGame($won, $guesses = null)
    {
        $this->games_played++;

        if ($won) {
            $this->games_won++;
            $this->current_streak++;

            if ($this->current_streak > $this->max_streak) {
                $this->max_streak = $this->current_streak;
            }

            // Update guess distribution
            if ($guesses) {
                $distribution = $this->guess_distribution ?? [];
                $distribution[$guesses] = ($distribution[$guesses] ?? 0) + 1;
                $this->guess_distribution = $distribution;
            }
        } else {
            $this->current_streak = 0;
        }

        $this->last_played_date = now()->toDateString();
        $this->save();
    }
}
