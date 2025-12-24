<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbCustomAvatar extends Model
{
    use HasFactory;

    protected $table = 'vb_custom_avatars';

    protected $primaryKey = 'userid';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(VbUser::class, 'userid', 'userid');
    }
}
