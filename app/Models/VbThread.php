<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbThread extends Model
{
    use HasFactory;

    protected $primaryKey = 'threadid';

    public function posts()
    {
        return $this->hasMany(VbPost::class, 'threadid', 'threadid');
    }

    public function forum()
    {
        return $this->belongsTo(VbForum::class, 'forumid', 'forumid');
    }

    public function creator()
    {
        return $this->belongsTo(VbUser::class, 'postuserid', 'userid');
    }
}
