<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VbForum extends Model
{
    use HasFactory;

    protected $table = 'vb_forums';

    protected $primaryKey = 'forumid';

    public $timestamps = false;

    /**
     * Forum IDs that are publicly accessible in the archive.
     * Forums not in this list require admin access.
     */
    public const PUBLIC_FORUM_IDS = [
        6, 12, 35, 36,      // Serious Business, Politics, Thread Bomber's Basement, Hall of Fame
        8, 34, 10, 41,      // HS Sports: Football, Scores and Updates, Wrestling, The Rest
        7, 32, 42, 15, 16,  // College and Pro Sports
    ];

    public static function isPublicForumId(int $forumId): bool
    {
        return in_array($forumId, self::PUBLIC_FORUM_IDS, true);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(VbThread::class, 'forumid')
            ->orderBy('lastpost', 'desc');
    }

    /**
     * Generate a URL-friendly slug from the forum title.
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->title);
    }

    /**
     * Get the route key for the model (ID-slug format for SEO).
     */
    public function getRouteKey(): string
    {
        return $this->forumid.'-'.$this->slug;
    }

    /**
     * Resolve the model from the route binding (extract ID from ID-slug format).
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        // Extract the ID from the beginning of the value
        $id = (int) $value;

        return $this->where('forumid', $id)->first();
    }
}
