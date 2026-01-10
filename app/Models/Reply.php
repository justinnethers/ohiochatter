<?php

namespace App\Models;

use App\Reppable;
use App\Traits\AutolinksUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class Reply extends Model
{
    use AutolinksUrls;
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

        // Update searchable_body before saving
        static::saving(function ($reply) {
            $reply->searchable_body = $reply->stripBlockquotes($reply->body);
        });

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

    /**
     * Strip blockquote tags and their content from HTML.
     */
    public function stripBlockquotes(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // Remove blockquote tags and their content (handles nested blockquotes too)
        $result = $html;
        $previous = '';

        // Keep stripping until no more blockquotes remain (handles nesting)
        while ($result !== $previous) {
            $previous = $result;
            $result = preg_replace('/<blockquote[^>]*>.*?<\/blockquote>/is', '', $result);
        }

        return $result;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
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
