<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer'
    ];

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

        return Content::whereIn('content_category_id', $categoryIds);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
