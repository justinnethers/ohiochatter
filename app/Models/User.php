<?php

namespace App\Models;

use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Messagable, Searchable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'name'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function searches(): HasMany
    {
        return $this->hasMany(Search::class);
    }

    public function getAvatarPathAttribute($avatar): string
    {
        if ($avatar) {
            // Use a configurable base URL for flexibility.
            return rtrim(config('app.avatar_base_url', config('app.url')), '/') . '/' . ltrim($avatar, '/');
        }
        return asset('images/avatars/default.png');
    }

    /**
     * Determine if the user has replied to the given thread.
     */
    public function hasRepliedTo(Thread $thread): bool
    {
        return Cache::rememberForever(
            "user-{$this->id}-replied-to-thread-{$thread->id}",
            function () use ($thread) {
                return $this->replies()->where('thread_id', $thread->id)->exists();
            }
        );
    }

    /**
     * Mark when the user has read the given thread.
     */
    public function read(Thread $thread): void
    {
        DB::table('threads_users_views')
            ->updateOrInsert(
                ['user_id' => $this->id, 'thread_id' => $thread->id],
                ['last_view' => Carbon::now()]
            );
    }

    /**
     * Retrieve the thread view record for the user.
     */
    public function threadViewRecord(Thread $thread): ?object
    {
        return DB::table('threads_users_views')
            ->where('user_id', $this->id)
            ->where('thread_id', $thread->id)
            ->first();
    }

    /**
     * Get the last time the user viewed the given thread.
     */
    public function lastViewedThreadAt(Thread $thread): ?Carbon
    {
        $record = $this->threadViewRecord($thread);
        return $record ? new Carbon($record->last_view) : null;
    }

    /**
     * Get the number of replies per page from configuration.
     */
    public function repliesPerPage(): int
    {
        return config('forum.replies_per_page');
    }

    /**
     * Update the user's last activity timestamp.
     */
    public function touchActivity(): void
    {
        $this->last_activity = $this->freshTimestamp();
        $this->save();
    }

    /**
     * Get the array representation of the model for Laravel Scout indexing.
     */
    public function toSearchableArray(): array
    {
        return [
            'username' => $this->username,
        ];
    }
}
