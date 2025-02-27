<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Puzzle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'publish_date',
        'answer',
        'word_count',
        'image_path',
        'category',
        'difficulty',
        'hint',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'publish_date' => 'date',
    ];

    /**
     * Get the puzzle for today
     *
     * @return Puzzle|null
     */
    public static function getTodaysPuzzle()
    {
        return self::where('publish_date', Carbon::today()->toDateString())->first();
    }

    /**
     * Get user progress for this puzzle
     */
    public function userProgress()
    {
        return $this->hasMany(UserGameProgress::class);
    }
}
