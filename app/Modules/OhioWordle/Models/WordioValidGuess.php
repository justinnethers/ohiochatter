<?php

namespace App\Modules\OhioWordle\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordioValidGuess extends Model
{
    protected $table = 'wordio_valid_guesses';

    protected $guarded = [];

    public function setWordAttribute(string $value): void
    {
        $this->attributes['word'] = strtoupper(trim($value));
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function approve(string $word, ?int $approverId = null): self
    {
        return self::firstOrCreate(
            ['word' => strtoupper(trim($word))],
            ['approved_by' => $approverId]
        );
    }

    public static function getAllWords(): array
    {
        return self::pluck('word')->toArray();
    }
}
