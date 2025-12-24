<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VbForum extends Model
{
    protected $table = 'vb_forums';

    protected $primaryKey = 'forumid';

    public $timestamps = false;

    public function threads(): HasMany
    {
        return $this->hasMany(VbThread::class, 'forumid')
            ->orderBy('lastpost', 'desc');
    }

}
