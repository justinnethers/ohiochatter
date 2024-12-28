<?php

namespace App\Models;

use App\Reppable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
