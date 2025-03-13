<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonymousGameProgress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
     * Get the puzzle related to this progress
     */
    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
