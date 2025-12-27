<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'demographics' => 'array',
        'founded_year' => 'integer'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function content()
    {
        return $this->morphMany(Content::class, 'locatable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function majorCities()
    {
        return $this->cities()->where('is_major', true);
    }

    public function zipCodes()
    {
        return $this->belongsToMany(ZipCode::class, 'county_zip_codes')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function getUrlAttribute()
    {
        return route('county.show', [
            'region' => $this->region->slug,
            'county' => $this->slug
        ]);
    }
}
