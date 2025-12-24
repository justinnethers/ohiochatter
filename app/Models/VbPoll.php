<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbPoll extends Model
{
    use HasFactory;

    protected $table = 'vb_polls';

    protected $primaryKey = 'pollid';

    public $timestamps = false;
}
