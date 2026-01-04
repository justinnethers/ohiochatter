<?php

namespace App\Modules\Pickem\Models;

use Database\Factories\PickemMatchupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickemMatchup extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PickemMatchupFactory::new();
    }

    protected $casts = [
        'points' => 'integer',
        'display_order' => 'integer',
    ];

    public function pickem(): BelongsTo
    {
        return $this->belongsTo(Pickem::class);
    }

    public function picks(): HasMany
    {
        return $this->hasMany(PickemPick::class);
    }

    public function getWinnerLabel(): ?string
    {
        return match ($this->winner) {
            'a' => $this->option_a,
            'b' => $this->option_b,
            'push' => 'Push (Tie)',
            default => null,
        };
    }

    public function getPickCountForOption(string $option): int
    {
        return $this->picks()->where('pick', $option)->count();
    }
}
