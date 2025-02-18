<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use Searchable, SoftDeletes;

    protected $table = 'content';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'gallery' => 'array'
    ];

    public function locatable()
    {
        return $this->morphTo();
    }

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
        return [
            'title' => $this->title,
            'body' => $this->body,
            'excerpt' => $this->excerpt,
            'content_type' => $this->contentType->name,
            'category' => $this->contentCategory->name,
            'location_type' => $this->locatable_type,
            'location_name' => $this->locatable?->name,
            'metadata' => $this->metadata,
            'published' => !is_null($this->published_at),
            'featured' => $this->featured,
            'author' => $this->author->username
        ];
    }
}
