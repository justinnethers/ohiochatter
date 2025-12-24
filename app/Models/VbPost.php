<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbPost extends Model
{
    use HasFactory;

    protected $table = 'vb_posts';

    protected $primaryKey = 'postid';

    public $timestamps = false;

    public function thread()
    {
        return $this->belongsTo(VbThread::class, 'threadid', 'threadid');
    }

    public function creator()
    {
        return $this->belongsTo(VbUser::class, 'userid', 'userid');
    }
}
