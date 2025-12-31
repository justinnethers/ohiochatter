<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'content_content_category')
            ->withTimestamps();
    }

    /**
     * @deprecated Use contents() instead
     */
    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function parent()
    {
        return $this->belongsTo(ContentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ContentCategory::class, 'parent_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Get all ancestors (parent, grandparent, etc.)
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    // Get full hierarchical path
    public function getPathAttribute()
    {
        return $this->ancestors()->reverse()->push($this);
    }

    // Get all descendants of the category
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    // Get all content from this category and its descendants
    public function allContent()
    {
        $descendantIds = $this->descendants()->pluck('id');
        $categoryIds = collect([$this->id])->merge($descendantIds);

        return Content::whereHas('contentCategories', function ($query) use ($categoryIds) {
            $query->whereIn('content_categories.id', $categoryIds);
        });
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
