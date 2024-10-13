<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbForum extends Model
{
    use HasFactory;

    protected $primaryKey = 'forumid';

    public function threads()
    {
        return $this->hasMany(VbThread::class, 'forumid')
            ->orderBy('lastpost', 'desc');
    }

}
