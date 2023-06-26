<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Reply;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];


    public function getAvatarPathAttribute($avatar)
    {
        if ($avatar) {
            return 'https://ohiochatter.com/'.$avatar;
        }

//        return asset($avatar ?: 'images/avatars/default.png');
        return asset('images/avatars/default.png');
    }

    public function hasRepliedTo($thread)
    {
        return Cache::rememberForever(
            "user-{$this->id}-replied-to-thread-{$thread->id}",
            function () use ($thread) {
                return (new Reply)->where('user_id', '=', $this->id)
                    ->where('thread_id', '=', $thread->id)->count();
            }
        );
    }
}
