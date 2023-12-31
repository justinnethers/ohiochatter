<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Neg extends Model
{
    protected $guarded = [];

    protected $with = ['user'];

    public function negged()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
