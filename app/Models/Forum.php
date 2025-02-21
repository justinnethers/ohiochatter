<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\Contracts\Sitemapable;

class Forum extends Model implements Sitemapable
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function toSitemapTag(): string | array|\Spatie\Sitemap\Tags\Url
    {
        return Url::create(route('forum.show', $this))
            ->setLastModificationDate(Carbon::create($this->updated_at))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.1);
    }
}
