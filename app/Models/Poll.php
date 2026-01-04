<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['pollOptions'];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->hasEnded();
    }

    public function pollOptions()
    {
        return $this->hasMany(PollOption::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
