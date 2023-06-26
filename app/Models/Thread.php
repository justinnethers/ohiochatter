<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = ['owner', 'forum'];

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function hasBeenRepliedToBy($user)
    {
        return $user->hasRepliedTo($this);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function path($extra = '')
    {
        return "/forums/{$this->forum->slug}/{$this->slug}" . $extra;
    }
}
