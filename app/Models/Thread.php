<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Thread extends Model implements Sitemapable
{
    use HasFactory, Reppable, Searchable, SoftDeletes;

    protected $guarded = [];

    protected $with = ['owner', 'forum', 'poll'];

    protected static function boot(): void
    {
        parent::boot();

        // Delete all replies when a thread is deleted.
        static::deleting(function (Thread $thread) {
            $thread->replies->each->delete();
        });

        // Update the slug attribute after creation.
        static::created(function (Thread $thread) {
            $thread->update(['slug' => $thread->title]);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function firstReply()
    {
        return $this->hasOne(Reply::class)->first();
    }

    public function lastReply(): HasOne
    {
        return $this->hasOne(Reply::class)->latest();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * Determine if the thread has updates for the given user.
     *
     * @param User $user
     * @return bool
     */
    public function hasUpdatesFor(User $user): bool
    {
        $lastVisit = $user->lastViewedThreadAt($this);

        // if the user has never viewed the thread
        if (! $lastVisit) {
            return true;
        }

        // if it's a new thread compare to the thread created_at date
        if (! $this->lastReply) {
            return $this->created_at->gt($lastVisit);
        }

        // if the last reply was by the logged in user
        if ($this->lastReply->user_id === auth()->id()) {
            return false;
        }

        return $this->lastReply->created_at->gt($lastVisit);
    }

    /**
     * Get the URL path for the thread.
     *
     * @param string $extra
     * @return string
     */
    public function path(string $extra = ''): string
    {
        return route('thread.show', [
                'forum'  => $this->forum->slug,
                'thread' => $this->slug,
            ]) . $extra;
    }

    /**
     * Get the number of replies for the thread.
     *
     * @return int
     */
    public function replyCount(): int
    {
        return $this->replies()
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Add a reply to the thread.
     *
     * @param  array  $reply
     * @return Model
     */
    public function addReply(array $reply): Model
    {
        return $this->replies()->create($reply);
    }

    /**
     * Set the slug attribute for the thread.
     *
     * @param string $value
     * @return void
     */
    public function setSlugAttribute(string $value): void
    {
        $slug = Str::slug($value);

        // Ensure the slug is unique.
        while (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-{$this->id}";
        }

        $this->attributes['slug'] = $slug;
    }

    /**
     * Get the array representation of the model for Laravel Scout indexing.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
        ];
    }

    public function toSitemapTag(): string | array|\Spatie\Sitemap\Tags\Url
    {
        return Url::create(route('thread.show', ['thread' => $this, 'forum' => $this->forum]))
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.1);
    }
}
