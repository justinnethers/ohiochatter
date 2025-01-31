<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Reply;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Messagable;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'name'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getAvatarPathAttribute($avatar)
    {
        if ($avatar) {
            return 'https://ohiochatter.com/'.$avatar;
        }

//        return asset($avatar ?: 'images/avatars/default.png');
        return asset('images/avatars/default.png');
    }

    public function hasRepliedTo(Thread $thread)
    {
        return Cache::rememberForever(
            "user-{$this->id}-replied-to-thread-{$thread->id}",
            function () use ($thread) {
                return (new Reply)->where('user_id', '=', $this->id)
                    ->where('thread_id', '=', $thread->id)->count();
            }
        );
    }

    // Mark when the user has read the given thread.
    public function read(Thread $thread): void
    {
        DB::table('threads_users_views')
            ->where('user_id', $this->id)
            ->where('thread_id', $thread->id)
            ->updateOrInsert(
                ['user_id' => $this->id, 'thread_id' => $thread->id],
                ['last_view' => Carbon::now()]
            );
    }

    // Get the last time the user viewed the given thread.
    public function lastViewedThreadAt($thread)
    {
        $view = DB::table('threads_users_views')
            ->where('user_id', $this->id)
            ->where('thread_id', $thread->id)
            ->first();

        return $view?->last_view;

    }

    public function repliesPerPage()
    {
        return config('forum.replies_per_page');
    }

    public function touchActivity()
    {
        $this->last_activity = $this->freshTimestamp();
        return $this->save();
    }

    public function lastVisitToThread(Thread $thread)
    {
//        return Cache::remember("user-" . auth()->id() . "-viewed-thread-{$thread->id}", 7200, function() use ($thread) {
            return DB::table('threads_users_views')
                ->where('user_id', '=', auth()->user()->id)
                ->where('thread_id', '=', $thread->id)
                ->first();
//        });
    }

//    public function getScoutKey(): mixed
//    {
//        return $this->username;
//    }
//
//    public function getScoutKeyName(): mixed
//    {
//        return 'username';
//    }

    public function toSearchableArray()
    {
        return [
            'username' => $this->username,
        ];
    }

    public function searches()
    {
        return $this->hasMany(Search::class);
    }
}
