<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbPost extends Model
{
    use HasFactory;

    public function thread()
    {
        return $this->belongsTo(VbThread::class, 'threadid', 'threadid');
    }

    public function creator()
    {
        return $this->belongsTo(VbUser::class, 'userid', 'userid');
    }
}
