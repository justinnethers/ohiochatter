<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class VbPost extends Model
{
    use HasFactory, Searchable;

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

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'pagetext' => $this->pagetext,
        ];
    }

    /**
     * Get the key name for Scout indexing.
     */
    public function getScoutKeyName(): string
    {
        return 'postid';
    }

    /**
     * Get the key for Scout indexing.
     */
    public function getScoutKey(): mixed
    {
        return $this->postid;
    }
}
