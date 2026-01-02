<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Content extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'content';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'gallery' => 'array',
        'blocks' => 'array',
    ];

    public function locatable()
    {
        return $this->morphTo();
    }

    public function contentCategories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_content_category')
            ->withTimestamps();
    }

    /**
     * @deprecated Use contentCategories() instead
     */
    public function contentCategory()
    {
        return $this->belongsTo(ContentCategory::class);
    }

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relatedContent()
    {
        return $this->belongsToMany(Content::class, 'related_content', 'content_id', 'related_id')
            ->withPivot('weight')
            ->orderBy('weight', 'desc');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    public function pendingRevision(): HasOne
    {
        return $this->hasOne(ContentRevision::class)->where('status', 'pending')->latest();
    }

    public function hasPendingRevision(): bool
    {
        return $this->revisions()->where('status', 'pending')->exists();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function toSearchableArray()
    {
        // Only include actual database columns for database driver compatibility
        return [
            'title' => $this->title,
            'body' => $this->body,
            'excerpt' => $this->excerpt,
        ];
    }
}
