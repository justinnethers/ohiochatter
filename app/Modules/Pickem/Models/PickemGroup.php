<?php

namespace App\Modules\Pickem\Models;

use Database\Factories\PickemGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PickemGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PickemGroupFactory::new();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pickems(): HasMany
    {
        return $this->hasMany(Pickem::class);
    }

    public function getLeaderboard(): Collection
    {
        $results = DB::table('users')
            ->select('users.*')
            ->selectRaw('COALESCE(SUM(
                CASE
                    WHEN pickem_matchups.winner IS NULL THEN 0
                    WHEN pickems.scoring_type = "simple" AND pickem_matchups.winner = pickem_picks.pick THEN 1
                    WHEN pickems.scoring_type = "simple" AND pickem_matchups.winner = "push" THEN 1
                    WHEN pickems.scoring_type = "weighted" AND pickem_matchups.winner = pickem_picks.pick THEN pickem_matchups.points
                    WHEN pickems.scoring_type = "weighted" AND pickem_matchups.winner = "push" THEN pickem_matchups.points
                    WHEN pickems.scoring_type = "confidence" AND pickem_matchups.winner = pickem_picks.pick THEN pickem_picks.confidence
                    WHEN pickems.scoring_type = "confidence" AND pickem_matchups.winner = "push" THEN pickem_picks.confidence
                    ELSE 0
                END
            ), 0) as total_points')
            ->join('pickem_picks', 'users.id', '=', 'pickem_picks.user_id')
            ->join('pickem_matchups', 'pickem_picks.pickem_matchup_id', '=', 'pickem_matchups.id')
            ->join('pickems', 'pickem_matchups.pickem_id', '=', 'pickems.id')
            ->where('pickems.pickem_group_id', $this->id)
            ->groupBy('users.id')
            ->orderByDesc('total_points')
            ->orderBy('users.username')
            ->get();

        // Calculate ranks with tie handling (competition ranking)
        $lastPoints = null;
        $lastRank = 0;

        return $results->map(function ($entry, $index) use (&$lastPoints, &$lastRank) {
            if ($entry->total_points !== $lastPoints) {
                $lastRank = $index + 1;
                $lastPoints = $entry->total_points;
            }
            $entry->rank = $lastRank;

            return $entry;
        });
    }
}
