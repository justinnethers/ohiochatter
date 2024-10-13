<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbUser extends Model
{
    use HasFactory;

    protected $with = ['avatar'];

    public function avatar()
    {
        return $this->hasOne(VbCustomAvatar::class, 'userid', 'userid');
    }

    public function avatarData()
    {
        return $this->avatar()->avatar_data;
    }
}
