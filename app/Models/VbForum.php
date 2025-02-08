<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VbForum extends Model
{
    protected $primaryKey = 'forumid';

    public function threads(): HasMany
    {
        return $this->hasMany(VbThread::class, 'forumid')
            ->orderBy('lastpost', 'desc');
    }

}
