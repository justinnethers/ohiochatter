<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasFactory, SoftDeletes, Reppable;

    protected $guarded = [];

    protected $with = ['owner', 'forum', 'replies', 'poll', 'reps', 'negs'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });

        static::created(function ($thread) {
            $thread->update(['slug' => $thread->title]);
        });
    }

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

    public function poll()
    {
        return $this->hasOne(Poll::class);
    }

    public function hasBeenRepliedToBy($user)
    {
        return $user->hasRepliedTo($this);
    }

    public function lastViewedByUser($user)
    {
        return $this->views()->where('user_id', $user->id)->first();
    }

    public function hasUpdatesFor($user)
    {
        $lastVisit = $user->lastVisitToThread($this);

        if (! $lastVisit) return true;

        return $this->updated_at > $lastVisit->last_view;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function path($extra = '')
    {
        return "/forums/{$this->forum->slug}/{$this->slug}" . $extra;
    }

    public function replyCount()
    {
        return $this->replies->count();
    }

    public function addReply($reply)
    {
        return $this->replies()->create($reply);
    }

    public function setSlugAttribute($value)
    {
        $slug = str_slug($value);

        while (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-" . $this->id;
        }

        $this->attributes['slug'] = $slug;
    }
}
