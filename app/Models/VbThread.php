<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class VbThread extends Model
{
    use HasFactory, Searchable;

    protected $table = 'vb_threads';

    protected $primaryKey = 'threadid';

    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany(VbPost::class, 'threadid', 'threadid');
    }

    public function forum()
    {
        return $this->belongsTo(VbForum::class, 'forumid', 'forumid');
    }

    public function creator()
    {
        return $this->belongsTo(VbUser::class, 'postuserid', 'userid');
    }

    /**
     * Generate a URL-friendly slug from the thread title.
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->title);
    }

    /**
     * Get the route key for the model (ID-slug format for SEO).
     */
    public function getRouteKey(): string
    {
        return $this->threadid . '-' . $this->slug;
    }

    /**
     * Resolve the model from the route binding (extract ID from ID-slug format).
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        // Extract the ID from the beginning of the value (before first dash with non-numeric)
        $id = (int) $value;

        return $this->where('threadid', $id)->first();
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    /**
     * Get the key name for Scout indexing.
     */
    public function getScoutKeyName(): string
    {
        return 'threadid';
    }

    /**
     * Get the key for Scout indexing.
     */
    public function getScoutKey(): mixed
    {
        return $this->threadid;
    }
}
