<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class Reply extends Model
{
    use HasFactory;
    use Reppable;
    use Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $with = ['owner'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            Cache::forget("thread-{$reply->thread_id}-reply-count");
        });

        static::deleted(function ($reply) {
            Cache::forget("thread-{$reply->thread_id}-reply-count");
        });

        static::restored(function ($reply) {
            Cache::forget("thread-{$reply->thread_id}-reply-count");
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
        ];
    }
}
