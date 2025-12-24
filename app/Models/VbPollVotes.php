<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VbPollVotes extends Model
{
    use HasFactory;

    protected $table = 'vb_poll_votes';

    public $timestamps = false;
}
