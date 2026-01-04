<?php

namespace App\Modules\Pickem\Models;

use App\Models\User;
use Database\Factories\PickemPickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickemPick extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PickemPickFactory::new();
    }

    protected $casts = [
        'confidence' => 'integer',
    ];

    protected $with = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchup(): BelongsTo
    {
        return $this->belongsTo(PickemMatchup::class, 'pickem_matchup_id');
    }

    public function isCorrect(): ?bool
    {
        if (! $this->matchup->winner) {
            return null;
        }

        if ($this->matchup->winner === 'push') {
            return true;
        }

        return $this->pick === $this->matchup->winner;
    }

    public function getPointsEarned(): int
    {
        if (! $this->isCorrect()) {
            return 0;
        }

        $pickem = $this->matchup->pickem;

        return match ($pickem->scoring_type) {
            'simple' => 1,
            'weighted' => $this->matchup->points,
            'confidence' => $this->confidence ?? 0,
            default => 0,
        };
    }
}
