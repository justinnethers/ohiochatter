<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Thread extends Model
{
    use HasFactory;
    use Reppable;
    use Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected $with = ['owner', 'forum', 'poll'];

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

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
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

//    public function path($extra = '')
//    {
//        return "/forums/{$this->forum->slug}/{$this->slug}" . $extra;
//    }

    public function path($extra = '')
    {
        return route('thread.show', [
                'forum' => $this->forum->slug,
                'thread' => $this->slug
            ]) . $extra;
    }

    public function replyCount()
    {
        return Cache::rememberForever("thread-{$this->id}-reply-count", function() {
            return $this->replies()
                ->whereNull('deleted_at')
                ->count();
        });
    }

    public function lastReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }

    public function addReply($reply)
    {
        return $this->replies()->create($reply);
    }

    public function setSlugAttribute($value)
    {
        $slug = Str::slug($value);

        while (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-" . $this->id;
        }

        $this->attributes['slug'] = $slug;
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
