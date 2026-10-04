<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Neg extends Model
{
    protected $guarded = [];

    protected $with = ['user'];

    public function negged()
    {
        return $this->morphTo();
    }

    /**
     * The user who gave the neg. Includes soft-deleted users and falls back to a
     * "Deleted user" placeholder when the user row no longer exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->withTrashed()
            ->withDefault(['username' => 'Deleted user']);
    }
}
