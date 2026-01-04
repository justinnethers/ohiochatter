<?php

namespace App\Modules\Pickem\Models;

use App\Models\User;
use Database\Factories\PickemCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickemComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PickemCommentFactory::new();
    }

    protected $with = ['owner'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pickem(): BelongsTo
    {
        return $this->belongsTo(Pickem::class);
    }
}
