<?php

namespace App\Modules\BuckEYE\Models;

use App\Models\User;
use Database\Factories\UserGameProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGameProgress extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return UserGameProgressFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'puzzle_id',
        'solved',
        'attempts',
        'guesses_taken',
        'previous_guesses',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'solved' => 'boolean',
        'previous_guesses' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the progress
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the puzzle related to this progress
     */
    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
