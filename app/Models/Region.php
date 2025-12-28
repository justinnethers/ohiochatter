<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Region extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    public function counties()
    {
        return $this->hasMany(County::class);
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, County::class);
    }

    public function content()
    {
        return $this->morphMany(Content::class, 'locatable');
    }

    public function categories()
    {
        return $this->hasManyThrough(
            ContentCategory::class,
            Content::class,
            'locatable_id',  // Foreign key on Content table
            'id',            // Foreign key on ContentCategory table
            'id',            // Local key on Region table
            'content_category_id'   // Local key on Content table
        )->where('locatable_type', Region::class)->distinct();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function toSearchableArray()
    {
        // Only include actual database columns for database driver compatibility
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
