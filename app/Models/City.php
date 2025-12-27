<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class City extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function content()
    {
        return $this->morphMany(Content::class, 'locatable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'is_major' => 'boolean',
        'coordinates' => 'array',
        'demographics' => 'array',
    ];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => 'city',
            'county' => $this->county->name,
            'region' => $this->county->region->name,
            'is_major' => $this->is_major
        ];
    }
}
