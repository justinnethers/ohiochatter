<?php

namespace App\Modules\Pickem\Models;

use App\Models\User;
use Database\Factories\PickemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pickem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PickemFactory::new();
    }

    protected $casts = [
        'picks_lock_at' => 'datetime',
        'is_finalized' => 'boolean',
    ];

    protected $with = ['owner', 'matchups'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(5);
            }
        });

        static::deleting(function ($pickem) {
            // Force delete matchups (they don't have soft deletes)
            $pickem->matchups()->delete();
            // Soft delete comments
            $pickem->comments->each->delete();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(PickemGroup::class, 'pickem_group_id');
    }

    public function matchups(): HasMany
    {
        return $this->hasMany(PickemMatchup::class)->orderBy('display_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PickemComment::class);
    }

    public function isLocked(): bool
    {
        return $this->picks_lock_at && $this->picks_lock_at->isPast();
    }

    public function isActive(): bool
    {
        return ! $this->isLocked();
    }

    public function path(): string
    {
        return route('pickem.show', $this);
    }

    public function getUserScore(User $user): int
    {
        $score = 0;

        foreach ($this->matchups as $matchup) {
            $pick = $matchup->picks()->where('user_id', $user->id)->first();

            if (! $pick || ! $matchup->winner) {
                continue;
            }

            // Push counts as correct
            $isCorrect = $matchup->winner === 'push' || $pick->pick === $matchup->winner;

            if ($isCorrect) {
                $score += match ($this->scoring_type) {
                    'simple' => 1,
                    'weighted' => $matchup->points,
                    'confidence' => $pick->confidence ?? 0,
                    default => 0,
                };
            }
        }

        return $score;
    }

    public function getMaxPossibleScore(): int
    {
        return match ($this->scoring_type) {
            'simple' => $this->matchups->count(),
            'weighted' => $this->matchups->sum('points'),
            'confidence' => array_sum(range(1, $this->matchups->count())),
            default => 0,
        };
    }

    /**
     * Get all unique users who submitted picks for this pickem.
     */
    public function getParticipantCount(): int
    {
        return PickemPick::whereIn('pickem_matchup_id', $this->matchups->pluck('id'))
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Check if a user has submitted picks for this pickem.
     */
    public function hasUserSubmitted(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return PickemPick::whereIn('pickem_matchup_id', $this->matchups->pluck('id'))
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Get the leaderboard for this specific pickem.
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $participants = PickemPick::whereIn('pickem_matchup_id', $this->matchups->pluck('id'))
            ->distinct('user_id')
            ->pluck('user_id');

        $scores = [];
        foreach ($participants as $userId) {
            $user = User::find($userId);
            if ($user) {
                $scores[] = [
                    'user' => $user,
                    'score' => $this->getUserScore($user),
                    'max' => $this->getMaxPossibleScore(),
                ];
            }
        }

        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scores, 0, $limit);
    }

    /**
     * Get the winner (highest scorer) for this pickem.
     */
    public function getWinner(): ?array
    {
        $leaderboard = $this->getLeaderboard(1);
        return $leaderboard[0] ?? null;
    }
}
