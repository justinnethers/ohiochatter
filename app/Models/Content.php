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
        'featured' => 'boolean'
    ];

    public function locatable()
    {
        return $this->morphTo();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relatedContent()
    {
        return $this->belongsToMany(Content::class, 'related_content', 'content_id', 'related_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'excerpt' => $this->excerpt,
            'type' => $this->contentType->name,
            'category' => $this->category->name,
            'tags' => $this->tags->pluck('name')->toArray(),
            'location_type' => $this->locatable_type,
            'location_name' => $this->locatable->name,
            'published' => !is_null($this->published_at),
            'featured' => $this->featured
        ];
    }
}
