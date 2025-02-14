<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Region extends Model
{
    use Searchable;

    protected $guarded = [];

    public function counties()
    {
        return $this->hasMany(County::class);
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, County::class);
    }

    public function contents()
    {
        return $this->morphMany(Content::class, 'locatable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => 'region'
        ];
    }
}
