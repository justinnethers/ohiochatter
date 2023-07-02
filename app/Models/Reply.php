<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use HasFactory, SoftDeletes, Reppable;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $with = ['owner', 'reps', 'negs'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
