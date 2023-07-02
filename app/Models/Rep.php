<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rep extends Model
{
    protected $guarded = [];

    protected $with = ['user'];

    public function repped()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
