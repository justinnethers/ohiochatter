<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbUser extends Model
{
    use HasFactory;

    protected $table = 'vb_users';

    protected $primaryKey = 'userid';

    public $timestamps = false;

    public function avatar()
    {
        return $this->hasOne(VbCustomAvatar::class, 'userid', 'userid');
    }

    public function avatarData()
    {
        return $this->avatar()->avatar_data;
    }
}
