<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GuideDraft extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'excerpt',
        'body',
        'content_category_id',
        'category_ids',
        'content_type_id',
        'locatable_type',
        'locatable_id',
        'featured_image',
        'gallery',
        'list_items',
        'list_settings',
        'blocks',
        'rating',
        'website',
        'address',
    ];

    protected $casts = [
        'gallery' => 'array',
        'list_items' => 'array',
        'list_settings' => 'array',
        'category_ids' => 'array',
        'blocks' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class);
    }

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    public function locatable(): MorphTo
    {
        return $this->morphTo();
    }
}